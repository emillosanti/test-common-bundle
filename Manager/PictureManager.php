<?php

namespace SAM\CommonBundle\Manager;

use SAM\AddressBookBundle\Entity\Company;
use Symfony\Component\Asset\Packages;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * Class PictureManager
 */
class PictureManager
{
    /**
     * @var Packages
     */
    private $assetPackages;

    /**
     * @var UploaderHelper
     */
    private $vichUploaderHelper;

    /**
     * @var CacheManager
     */
    private $imagineCacheManager;

    /**
     * @var array
     */
    private $defaultParams;

    private $prefixUrl;

    private $contactsDir;

    private $companiesDir;

    /**
     * PictureManager constructor.
     *
     * @param Packages $assetPackages
     */
    public function __construct(Packages $assetPackages)
    {
        $this->assetPackages = $assetPackages;
        $this->defaultParams = [
            'fieldName' => 'pictureFile',
            'default' => 'unknown-user.jpg',
            'size' => '100',
            'imagineFilter' => null,
            'placeholder' => true,
        ];
    }

    /** 
     * $params array
     */
    public function setParameters($prefixUrl, $contactsDir, $companiesDir) 
    {
        $this->prefixUrl = $prefixUrl;
        $this->contactsDir = $contactsDir;
        $this->companiesDir = $companiesDir;
    }

    /**
     * @required
     *
     * @param UploaderHelper $vichUploaderHelper
     *
     * @return $this
     */
    public function setVichUploaderHelper(UploaderHelper $vichUploaderHelper)
    {
        $this->vichUploaderHelper = $vichUploaderHelper;

        return $this;
    }

    /**
     * @required
     *
     * @param CacheManager $imagineCacheManager
     *
     * @return $this
     */
    public function setImagineCacheManager(CacheManager $imagineCacheManager)
    {
        $this->imagineCacheManager = $imagineCacheManager;

        return $this;
    }

    /**
     * @param object $object
     * @param array  $params
     *
     * @return string
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getPicture($object, $params = [])
    {
        // Don't change the order, ITS REALLY IMPORTANT, $params values erase $defaultParams
        $params = array_merge($this->defaultParams, $params);

        if ($object instanceof Company) {
            if ($object->getLogo()) {
                if (!empty($params['imagineFilter'])) {
                    return $this->imagineCacheManager->getBrowserPath(
                        $this->vichUploaderHelper->asset($object, 'logoFile'),
                        str_replace('user_contact_', 'company_', $params['imagineFilter']) // replace user_contact liip filter by company liip filter
                    );
                }

                return $this->prefixUrl . '/' . $this->companiesDir . $this->vichUploaderHelper->asset($object, 'logoFile');
            } else if ($object->getLogoAlternative()) {
                return $object->getLogoAlternative();
            }
        } else if (method_exists($object, 'getPicture')) {
            if ($object->getPicture()) {
                if (!empty($params['imagineFilter'])) {
                    return $this->imagineCacheManager->getBrowserPath(
                        $this->vichUploaderHelper->asset($object, $params['fieldName']), 
                        $params['imagineFilter']
                    );
                } else {
                    return $this->prefixUrl . '/' . $this->contactsDir . $this->vichUploaderHelper->asset($object, $params['fieldName']);
                }
            } else if (method_exists($object, 'getPictureAlternative') && $object->getPictureAlternative()) {
                return $object->getPictureAlternative();
            } else if (method_exists($object, 'getCompany') && true === $params['placeholder']) {
                if ($object->getCompany()) {
                    if ($object->getCompany()->getLogo()) {
                        if (!empty($params['imagineFilter'])) {
                            return $this->imagineCacheManager->getBrowserPath(
                                $this->vichUploaderHelper->asset($object->getCompany(), 'logoFile'),
                                str_replace('user_contact_', 'company_', $params['imagineFilter']) // replace user_contact liip filter by company liip filter
                            );
                        }

                        return $this->prefixUrl . '/' . $this->companiesDir . $this->vichUploaderHelper->asset($object->getCompany(), 'logoFile');
                    } else if ($object->getCompany()->getLogoAlternative()) {
                        return $object->getCompany()->getLogoAlternative();
                    }
                }
            }
        }

        if ($object && is_string($object) && !empty($object)) {
            return $this->prefixUrl . '/' . ($object instanceof Company ? $this->companiesDir : $this->contactsDir) . $this->assetPackages->getUrl($object);
        } else {
            if (true === $params['placeholder']) {
                if (!empty($params['imagineFilter'])) {
                    return $this->imagineCacheManager->getBrowserPath(
                        $this->assetPackages->getUrl($params['default']),
                        $params['imagineFilter']
                    );
                } else {
                    return $this->prefixUrl . '/' . ($object instanceof Company ? $this->companiesDir : $this->contactsDir) . $this->assetPackages->getUrl($params['default']);
                }
            } else {
                return null;
            }
        }
    }
}
