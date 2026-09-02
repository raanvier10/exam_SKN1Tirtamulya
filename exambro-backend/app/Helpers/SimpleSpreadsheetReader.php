<?php

namespace App\Helpers;

use ZipArchive;

class SimpleSpreadsheetReader
{
    /**
     * Read CSV or XLSX file and return an array of associative rows based on header row.
     *
     * @param \Illuminate\Http\UploadedFile|string $file
     * @return array<int, array<string, mixed>>
     */
    public static function read($file): array
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $ext = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : ($file->getClientOriginalExtension() ?: 'csv'));

        $rawRows = [];

        if ($ext === 'xlsx') {
            $rawRows = self::readXlsx($path);
        } else {
            $rawRows = self::readCsv($path);
        }

        if (empty($rawRows)) {
            return [];
        }

        // Clean headers: trim, lowercase, remove UTF-8 BOM, replace space with underscore
        $rawHeaders = array_shift($rawRows);
        $headers = [];
        foreach ($rawHeaders as $h) {
            $cleaned = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim((string)$h));
            $cleaned = strtolower(str_replace([' ', '-'], '_', $cleaned));
            $headers[] = $cleaned;
        }

        $result = [];
        foreach ($rawRows as $row) {
            if (empty(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $idx => $header) {
                if ($header !== '') {
                    $assoc[$header] = isset($row[$idx]) ? trim((string)$row[$idx]) : null;
                }
            }
            $result[] = $assoc;
        }

        return $result;
    }

    protected static function readCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = ',';
            if ($firstLine !== false) {
                $semi = substr_count($firstLine, ';');
                $comma = substr_count($firstLine, ',');
                $tab = substr_count($firstLine, "\t");
                if ($semi > $comma && $semi > $tab) {
                    $delimiter = ';';
                } elseif ($tab > $comma && $tab > $semi) {
                    $delimiter = "\t";
                }
            }

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    protected static function readXlsx(string $path): array
    {
        $rows = [];
        $zip = new ZipArchive();
        if ($zip->open($path) === true) {
            $sharedStrings = [];
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml) {
                $xml = @simplexml_load_string($sharedStringsXml);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string)$si->t;
                        } elseif (isset($si->r)) {
                            $text = '';
                            foreach ($si->r as $r) {
                                $text .= (string)$r->t;
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml) {
                $xml = @simplexml_load_string($sheetXml);
                if ($xml && isset($xml->sheetData->row)) {
                    foreach ($xml->sheetData->row as $rowNode) {
                        $row = [];
                        $currentCol = 0;
                        foreach ($rowNode->c as $cell) {
                            $ref = (string)$cell['r'];
                            preg_match('/([A-Z]+)(\d+)/', $ref, $matches);
                            $colIndex = self::colIndexToNumber($matches[1] ?? 'A');
                            
                            while ($currentCol < $colIndex) {
                                $row[] = '';
                                $currentCol++;
                            }

                            $type = (string)$cell['t'];
                            $val = (string)$cell->v;

                            if ($type === 's' && isset($sharedStrings[(int)$val])) {
                                $cellVal = $sharedStrings[(int)$val];
                            } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                                $cellVal = (string)$cell->is->t;
                            } else {
                                $cellVal = $val;
                            }

                            $row[] = $cellVal;
                            $currentCol++;
                        }
                        $rows[] = $row;
                    }
                }
            }
            $zip->close();
        }
        return $rows;
    }

    protected static function colIndexToNumber(string $col): int
    {
        $len = strlen($col);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num - 1;
    }
}
