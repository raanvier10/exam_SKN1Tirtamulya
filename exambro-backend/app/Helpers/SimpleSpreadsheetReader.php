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
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File Excel (.xlsx) tidak valid atau rusak. Pastikan format benar-benar .xlsx atau .csv.');
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml && stripos($sharedStringsXml, '<!DOCTYPE') === false && stripos($sharedStringsXml, '<!ENTITY') === false) {
            $xml = @simplexml_load_string($sharedStringsXml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
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
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (is_string($entryName) && preg_match('#^xl/worksheets/sheet\d*\.xml$#i', $entryName)) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        if ($sheetXml && stripos($sheetXml, '<!DOCTYPE') === false && stripos($sheetXml, '<!ENTITY') === false) {
            $xml = @simplexml_load_string($sheetXml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
            if ($xml && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $rowNode) {
                    $row = [];
                    $currentCol = 0;
                    foreach ($rowNode->c as $cell) {
                        $ref = (string)$cell['r'];
                        if ($ref !== '' && preg_match('/([A-Z]+)(\d+)/', $ref, $matches)) {
                            $colIndex = self::colIndexToNumber($matches[1]);
                        } else {
                            $colIndex = $currentCol;
                        }

                        while ($currentCol < $colIndex) {
                            $row[] = '';
                            $currentCol++;
                        }

                        $type = (string)$cell['t'];
                        $val = (string)$cell->v;

                        if ($type === 's' && isset($sharedStrings[(int)$val])) {
                            $cellVal = $sharedStrings[(int)$val];
                        } elseif ($type === 'inlineStr') {
                            if (isset($cell->is->t)) {
                                $cellVal = (string)$cell->is->t;
                            } elseif (isset($cell->is->r)) {
                                $text = '';
                                foreach ($cell->is->r as $r) {
                                    $text .= (string)$r->t;
                                }
                                $cellVal = $text;
                            } else {
                                $cellVal = '';
                            }
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

    /**
     * Parse spreadsheet date/time string or Excel serial number safely.
     */
    public static function parseDateTime(mixed $value): ?\Carbon\Carbon
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $serial = (float) $raw;
            if ($serial > 25569 && $serial < 100000) {
                // Compute in UTC to avoid pre-1970 historical LMT offsets (e.g. Asia/Jakarta +07:07:12 in 1899)
                return \Carbon\Carbon::create(1899, 12, 30, 0, 0, 0, 'UTC')
                    ->addSeconds((int) round($serial * 86400))
                    ->shiftTimezone(config('app.timezone'));
            }
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            try {
                return \Carbon\Carbon::createFromFormat($fmt, $raw, config('app.timezone'));
            } catch (\Throwable) {
            }
        }

        try {
            return \Carbon\Carbon::parse($raw, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
