<?php

namespace SAM\CommonBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class AbstractManager
 */
abstract class AbstractManager
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var string
     */
    protected $projectDirectory;

    /**
     * @var string
     */
    protected $siteName;

    /**
     * @var array
     */
    protected $styles = [
            'heading' => [
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => TRUE
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '44546a']
                ],
                'borders' => [
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
                ]
            ],
            'body' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'color' => ['rgb' => '5d6e8d']
                ]
            ]
        ];

    /**
     * Default row height
     * @var integer
     */
    protected $rowHeight = 30;

    /**
     * Default sheet
     * @var integer
     */
    protected $defaultSheet = 0;

    /**
     * AbstractManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     * @param string $projectDirectory
     * * @param string $siteName
     */
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        string $projectDirectory,
        string $siteName
    )
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->projectDirectory = $projectDirectory;
        $this->siteName = $siteName;
    }

    /**
     * @param array $criterias
     *
     * @return Xlsx
     *
     * @throws Exception
     */
    public function export($criterias = [])
    {
        $document = new Spreadsheet();
        $this->setDefaultStyle($document);
        $this->addHeader($document);
        $this->addLogo($document);
        $this->addRows($document, $criterias);
        $document->setActiveSheetIndex(0);

        return new Xlsx($document);
    }

    /**
     * @param Spreadsheet $document
     * @param array $criterias
     *
     * @return Spreadsheet
     *
     * @throws Exception
     */
    abstract protected function addRows(Spreadsheet $document, $criterias = []);

    /**
     * @param Spreadsheet $document
     *
     * @return Spreadsheet
     *
     * @throws Exception
     */
    abstract protected function addHeader(Spreadsheet $document);

    /**
     * @param Spreadsheet $document
     *
     * @throws Exception
     */
    protected function setDefaultStyle(Spreadsheet $document)
    {
        $document->getDefaultStyle()->applyFromArray([
            'font' => [
                'color' => ['rgb' => '000000'],
                'size'  => 10,
                'name'  => 'Arial',
            ],
            'alignment' => [
                'wrapText' => TRUE
            ],
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_NONE]
            ]
        ]);
    }

    /**
     * Load logo into sheet
     * @param Spreadsheet $document   
     */
    protected function addLogo($document)
    {
        $sheet = $document->getSheet(0);
        $fileSystem = new Filesystem();
        $path = $this->projectDirectory . '/web/img/logo.png';
        if ($fileSystem->exists($path)) {
            $drawing = new Drawing();
            $drawing->setName('Logo '.$this->siteName);
            $drawing->setPath($path);
            $drawing->setHeight(36);
            $drawing->setHeight(80);
            $drawing->setOffsetY(5);
            $drawing->setCoordinates('B1');
            $drawing->setWorksheet($sheet);
        }
    }

    /**
     * Format columns of the sheet
     * 
     * @param  Sheet $sheet   
     * @param  array $settings
     * @param  array $headings
     */
    protected function formatColumns($sheet, $settings, $headings)
    {
        foreach ($settings as $setting) {
            if (isset($setting['value'])) {
                $sheet->mergeCells(sprintf('%s1:%s1', strtoupper($setting['begin']), $setting['end']));
                $sheet->mergeCells(sprintf('%s2:%s2', strtoupper($setting['begin']), $setting['end']));
                $sheet->getCell(strtoupper($setting['begin']).'2')->setValue($setting['value'])->getStyle()->applyFromArray($this->styles['heading']);
            } else {
                $sheet->mergeCells(sprintf('%s1:%s2', strtoupper($setting['begin']), $setting['end']));
            }
        }

        $sheet->getRowDimension('1')->setRowHeight(40);
        $sheet->getRowDimension('2')->setRowHeight(30);
        $sheet->getRowDimension('3')->setRowHeight(40);

        $column = 2;
        foreach ($headings as $heading) {
            $sheet->getCellByColumnAndRow($column, 3)->setValue($heading['content'])->getStyle()->applyFromArray($this->styles['heading']);
            $col = $sheet->getCellByColumnAndRow($column, 3)->getColumn();
            $sheet->getColumnDimension($col)->setWidth($heading['width']);
            $column++;
        }
    }
}
