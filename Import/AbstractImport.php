<?php

namespace SAM\CommonBundle\Import;

use SAM\AddressBookBundle\Entity\ContactMerged;
use SAM\AddressBookBundle\Repository\ContactMergedRepositoryInterface;
use SAM\CommonBundle\Exception\InvalidFileFormatException;
use SAM\CommonBundle\Utils\Utils;
use SAM\AddressBookBundle\Entity\CompanyMetric;
use SAM\AddressBookBundle\Entity\Company;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SAM\SearchBundle\Manager\SearchEngineManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractImport
 */
abstract class AbstractImport
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var string
     */
    protected $mainEntityName;

    protected $entities;

    protected $siteName;

    protected $usersFields = ['R1', 'R2', 'R3', 'R4', 'R5'];

    protected $user;

    /** @var SearchEngineManager */
    protected $searchEngineManager;

    /**
     * AbstractImport constructor.
     *
     * @param ObjectManager $om
     * @param SearchEngineManager $searchEngineManager
     */
    public function __construct(ObjectManager $om, SearchEngineManager $searchEngineManager)
    {
        $this->om = $om;
        $this->dateFormat = 'd/m/Y';
        $this->searchEngineManager = $searchEngineManager;
    }

    public function setConfiguration($entities, $siteName, $mainEntityName)
    {
        $this->entities = $entities;
        $this->mainEntityName = $mainEntityName;
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param UploadedFile $file
     * @param UserInterface $user
     * @param array $preferences
     *
     * @throws InvalidFileFormatException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function process(UploadedFile $file, UserInterface $user, array $preferences = [])
    {
        $this->user = $user;

        $rows = $this->normalize($file);

        // handle user preferences
        foreach ($preferences as $key => $value) {
            foreach ($rows as &$row) {
                $row[$key] = $value;
            }
        }

        $this->validate($rows);
        $this->import($rows);
    }

    /**
     * @param UploadedFile $file
     *
     * @return array
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function normalize(UploadedFile $file, $startingLine = 4)
    {
        $entities = [];
        $reader = new Xlsx();
        $document = $reader->load($file->getPathname());
        $sheet = $document->getSheet(0);
        foreach ($sheet->getRowIterator($startingLine) as $row) {
            $allEmpty = true;
            foreach ($row->getCellIterator() as $cel) {
                $value = trim($cel->getValue());
                if (!empty($value)) {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }
            $entity = $this->loadRow($sheet, $row);
            $entities[] = $entity;
        }

        return $entities;
    }

    abstract protected function loadRow($sheet, $row);

     /**
     * @param array $data
     *
     * @throws InvalidFileFormatException
     *
     * @return bool
     */
    protected function validate($data)
    {
        $metrics = [
            'ca' => 'CA',
            'ebitda' => 'EBITDA',
            'ebit' => 'EBIT',
            'employeesCount' => 'Effectifs',
            'businessValue' => 'VE',
            'dfn' => 'DFN',
        ];

        foreach ($data as $row) {
            if (!empty($row['id']) && !is_numeric($row['id'])) {
                throw new InvalidFileFormatException(sprintf('Identifiant invalide à la ligne %d', $row['row']));
            }
            if (!$this->isValidDate($row['startDate'])) {
                throw new InvalidFileFormatException(sprintf(
                    'Le format de la date "%s" est invalide à la ligne %d',
                    $row['startDate'],
                    $row['row']
                ));
            }
            if (!empty($row['metric']['year'])) {
                if (!is_numeric($row['metric']['year'])) {
                    throw new InvalidFileFormatException(sprintf('Le champ "Année" est invalide à la ligne %d', $row['row']));
                }
                foreach ($metrics as $key => $fieldName) {
                    $value = $row['metric'][$key];
                    if (!empty($value) && !is_numeric($value)) {
                        throw new InvalidFileFormatException(sprintf(
                            'Le champ "%s" est invalide à la ligne %d',
                            $fieldName,
                            $row['row']
                        ));
                    }
                }
            }
            foreach ($this->usersFields as $user) {
                if (!empty($row[$user]) && !$this->isExistingUser($row[$user])) {
                    throw new InvalidFileFormatException(sprintf(
                        'L\'utilisateur "%s" n\'existe pas à la ligne %d',
                        $row[$user],
                        $row['row']
                    ));
                }
            }
            if (empty($row['company']['name'])) {
                throw new InvalidFileFormatException(sprintf(
                    'Le nom de la société ne peut être vide à la ligne %d',
                    $row['row']
                ));
            }
            if (!$this->isExistingSector($row['company']['sector'])) {
                throw new InvalidFileFormatException(sprintf(
                    'Le secteur "%s" n\'existe pas à la ligne %d',
                    $row['company']['sector'],
                    $row['row']
                ));
            }
            if (!empty($row['company']['nafCode']) && !$this->isExistingNaf($row['company']['nafCode'])) {
                throw new InvalidFileFormatException(sprintf(
                    'Le code NAF "%s" n\'existe pas à la ligne %d',
                    $row['company']['nafCode'],
                    $row['row']
                ));
            }
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return bool
     */
    abstract protected function import($data);

    /**
     * @param int $id
     *
     * @return
     */
    protected function getOrCreateMainEntity($id, $property = null)
    {
        $entity = null;
        if ($id) {
            $entity = $property ? $this->om->getRepository($this->mainEntityName)->findOneBy([ $property => $id ]) : $this->om->getRepository($this->mainEntityName)->find($id);
        }

        if (null === $entity) {
            $mainObjectClass = $this->entities[$this->mainEntityName]['class'];
            $entity = new $mainObjectClass();
        }

        return $entity;
    }

    /**
     * @param string $companyName
     *
     * @return Company
     */
    protected function getOrCreateCompany($siren, $companyName)
    {
        $company = null;
        if ($siren) {
            $uow = $this->om->getUnitOfWork();
            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                if ($entity instanceof Company) {
                    if ($entity->getSiren() === $siren) {
                        $company = $entity;
                        break;
                    }
                }
            }

            if (null === $company) {
                $company = $this->om->getRepository('company')->findOneBy(['siren' => $siren]);
            }
        } else {
            $slug = Utils::sanitizeSlug(strtolower(trim($companyName)));

            $uow = $this->om->getUnitOfWork();
            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                if ($entity instanceof Company) {
                    $companySlug = Utils::sanitizeSlug(strtolower(trim($entity->getName())));
                    if ($companySlug === $slug) {
                        $company = $entity;
                        break;
                    }
                }
            }

            if (null === $company) {
                $company = $this->om->getRepository('company')->findOneBy(['slug' => $slug]);
            }
        }

        if (null === $company) {
            $companyClass = $this->entities['company']['class'];
            $company = new $companyClass();
            $company->setName($companyName);
        }

        $company->setVisible(true);
        if ($siren) {
            $company->setSiren($siren);
        }

        return $company;
    }

    /**
     * @param string $sector
     *
     * @return bool
     */
    protected function isExistingSector($sector)
    {
        return null !== $this->getSector($sector);
    }

    /**
     * @param string $sector
     *
     * @return BusinessSector
     */
    protected function getSector($sector)
    {
        return $this->om->getRepository('business_sector')->findOneBy(['name' => $sector]);
    }

    /**
     * Update users
     * 
     * @param  [type] $mainEntity [description]
     * @param  [type] $row        [description]
     */
    protected function updateUsers($mainEntity, $row, $currentUser)
    {
        if (method_exists($mainEntity, 'setManager') && method_exists($mainEntity, 'getManager') && $currentUser) {
            $mainEntity->setManager($currentUser);
        }

        if (!$mainEntity->getUsers()->contains($currentUser)) {
            $mainEntity->addUser($currentUser);
        }

        $users = new ArrayCollection();
        foreach ($this->usersFields as $field) {
            if (!empty($row[$field])) {
                $user = $this->getUser($row[$field]);
                $users->add($user);
                if (!$mainEntity->getUsers()->contains($user)) {
                    $mainEntity->addUser($user);
                }
            }
        }
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    protected function isExistingUser($code)
    {
        return null !== $this->getUser($code);
    }

    /**
     * @param string $code
     *
     * @return User
     */
    protected function getUser($code)
    {
        return $this->om->getRepository('user')->findOneBy(['code' => $code]);
    }

    /**
     * @param string $operationType
     *
     * @return bool
     */
    protected function isExistingOperationType($operationType)
    {
        return null !== $this->getOperationType($operationType);
    }

    /**
     * @param string $operationType
     *
     * @return OperationType
     */
    protected function getOperationType($operationType)
    {
        return $this->om->getRepository('operation_type')->findOneBy(['name' => $operationType]);
    }

    /**
     * @param string $category
     *
     * @return bool
     */
    protected function isExistingSourcingCategory($category)
    {
        return null !== $this->getSourcingCategory($category);
    }

    /**
     * @param string $category
     *
     * @return SourcingCategory
     */
    protected function getSourcingCategory($category)
    {
        return $this->om->getRepository('sourcing_category')->findOneBy(['name' => $category]);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    protected function isValidDate($value) {
        if ($value instanceof \DateTime) {
            return true;
        }

        return false !== preg_match('#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#', $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function isExistingCompany($name)
    {
        return null !== $this->getCompany($name);
    }

    /**
     * @param string $name
     *
     * @return Company
     */
    protected function getCompany($name)
    {
        return $this->om->getRepository('company')->findOneBy(['name' => $name]);
    }

    /**
     * @param string $fullName
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function isExistingContactMerged($fullName)
    {
        return null !== $this->getContactMerged($fullName);
    }

    /**
     * @param string $fullName
     *
     * @return ContactMerged
     *
     */
    protected function getContactMerged($fullName)
    {
        return $this->searchEngineManager->getDoctrineRepository(ContactMergedRepositoryInterface::class)
            ->findOneByFullName($fullName);
    }

    /**
     * @param string $nafCode
     *
     * @return bool
     */
    protected function isExistingNaf($nafCode)
    {
        return null !== $this->getNaf($nafCode);
    }

    /**
     * Get naf code by code
     * @param  string $nafCode
     * @return NafCode
     */
    protected function getNaf($nafCode)
    {
        return $this->om->getRepository('naf_code')->findOneByCode($nafCode);
    }

     /**
     * @param Worksheet $sheet
     * @param $coords
     *
     * @return string
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function getDate(Worksheet $sheet, $coords)
    {
        $date = $sheet->getCell($coords)->getValue();
        // Check if we have a formula, if yes, get the calculated value
        if (strpos($date, '=') !== false) {
            $date = $sheet->getCell($coords)->getCalculatedValue();
        }

        if (!is_numeric($date)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($date);
        }

        if (empty($date)) {
            return null;
        }

        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
    }

    protected function updateCompany($mainEntity, $row)
    {
        $company = $this->getOrCreateCompany($row['id'], $row['company']['name']);
        $company->setName(ucwords(strtolower($row['company']['name'])));

        if (!empty($row['company']['description'])) {
            $company->setDescription($row['company']['description']);
        }
        if (!empty($row['company']['sector'])) {
            $company->setSector($this->getSector($row['company']['sector']));
        }
        if (!empty($row['company']['phone'])) {
            $company->setPhoneNumber($row['company']['phone']);
        }
        if ($row['company']['shareholding']) {
            $company->setShareholding($row['company']['shareholding']);;
        }
        if (!empty($row['company']['website'])) {
            $company->setWebsite($row['company']['website']);
        }

        $companyAddress = null;
        if ($company->getAddress()) {
            $companyAddress = $company->getAddress();
        } else if (!empty($row['company']['address'])) {
            $companyAddress = new $this->entities['address']['class']();
            $company->setAddress($companyAddress);
        }

        if ($companyAddress) {
            $companyAddress
                ->setAddress($row['company']['address'])
                ->setZipCode($row['company']['zipCode'])
                ->setCity($row['company']['city'])
                ->setCountry($row['company']['country']);
        }

        if ($row['company']['nafCode']) {
            $company->setNafCode($this->getNaf($row['company']['nafCode']));
        }

        $mainEntity->setCompany($company);
    }

    protected function updateCompanyMetrics($company, $row, $type)
    {
        if (is_numeric($row['metric']['year'])) {
            $yearExists = false;
            foreach ($company->getMetrics() as $companyMetric) {
                if ($companyMetric->getYear() == $row['metric']['year']) {
                    $companyMetric
                        ->setCa($row['metric']['ca'] === null ? null : $row['metric']['ca'])
                        ->setEbitda($row['metric']['ebitda'] === null ? null : $row['metric']['ebitda'])
                        ->setEbit($row['metric']['ebit'] === null ? null : $row['metric']['ebit'])
                        ->setEmployeesCount($row['metric']['employeesCount'] === null ? null : $row['metric']['employeesCount'])
                        ->setEnterpriseValue($row['metric']['businessValue'] === null ? null : $row['metric']['businessValue'])
                        ->setNetFinancialDebt($row['metric']['dfn'] === null ? null : $row['metric']['dfn'])
                        ->setType($type);

                    $yearExists = true;
                    break;
                }
            }
            
            if (!$yearExists) {
                $this->createCompanyMetric($company, $row, $type);
            }
        }
    }

    protected function createCompanyMetric($company, $row, $type)
    {
        $metric = new $this->entities['company_metric']['class']();
        $metric->setCompany($company)
            ->setYear($row['metric']['year'])
            ->setCa($row['metric']['ca'])
            ->setEbitda($row['metric']['ebitda'])
            ->setEbit($row['metric']['ebit'])
            ->setEmployeesCount($row['metric']['employeesCount'])
            ->setEnterpriseValue($row['metric']['businessValue'])
            ->setNetFinancialDebt($row['metric']['dfn'])
            ->setType($type);

        $company->addMetric($metric);
    }
}
