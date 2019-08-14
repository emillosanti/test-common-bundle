<?php

namespace SAM\CommonBundle\Manager;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class AbstractMetricsManager
 */
abstract class AbstractMetricsManager
{
    protected $letters = [
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z'
        ];

    /**
     * @param  array  $map [description]
     * @return Xlsx
     */
    public function export(array $map)
    {
        $document = new Spreadsheet();
        $document->setActiveSheetIndex(0);
        $sheet = null;
        $sheetIndex = 0;

        foreach ($map as $title => $metrics) {
            $sheetIndex++;
            if (null === $sheet) {
                $sheet = $document->getActiveSheet();
            } else {
                $sheet = $document->createSheet($sheetIndex);
            }
            if (strlen($title) > 31) {
                $title = substr($title, 0, 30) . '.';
            }
            $sheet->setTitle($title);
            foreach ($metrics as $index => $row) {
                $col = $this->letters[$index];
                $sheet->getCell($col . '1')->setValue($row['label']);
                $sheet->getCell($col . '2')->setValue($row['count']);
            }
        }

        return new Xlsx($document);
    }
}
