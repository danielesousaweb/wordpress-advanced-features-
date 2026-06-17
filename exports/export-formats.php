<?php
// -------------------------------------------------
// Formatting functions
// -------------------------------------------------
 
function export_as_csv($headers, $rows, $name) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $name . '.csv"');
    header('Pragma: no-cache');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel opens it correctly
 
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers, ';');
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $h) {
            $line[] = isset($row[$h]) ? $row[$h] : '';
        }
        fputcsv($out, $line, ';');
    }
    fclose($out);
}
 
function export_as_json($rows, $name) {
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $name . '.json"');
    header('Pragma: no-cache');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
 
function export_as_xml($rows, $name, $post_type) {
    header('Content-Type: text/xml; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $name . '.xml"');
    header('Pragma: no-cache');
 
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><export/>');
    $xml->addAttribute('type',  $post_type);
    $xml->addAttribute('total', count($rows));
    $xml->addAttribute('date',  date('Y-m-d H:i:s'));
 
    foreach ($rows as $row) {
        $item = $xml->addChild('item');
        foreach ($row as $field => $value) {
            $tag = preg_replace('/[^a-zA-Z0-9_]/', '_', $field);
            $tag = ltrim($tag, '0123456789');
            if (empty($tag)) $tag = 'field';
            $item->addChild($tag, htmlspecialchars((string)$value, ENT_XML1, 'UTF-8'));
        }
    }
 
    echo $xml->asXML();
}
 
function export_as_xlsx($headers, $rows, $name) {
    $sheet_rows = '';
 
    // Header row
    $sheet_rows .= '<row r="1">';
    foreach ($headers as $ci => $h) {
        $col         = xlsx_col_letter($ci + 1);
        $h_safe      = htmlspecialchars((string)$h, ENT_XML1, 'UTF-8');
        $sheet_rows .= '<c r="' . $col . '1" t="inlineStr"><is><t>' . $h_safe . '</t></is></c>';
    }
    $sheet_rows .= '</row>';
 
    // Data rows
    foreach ($rows as $ri => $row) {
        $rn          = $ri + 2;
        $sheet_rows .= '<row r="' . $rn . '">';
        foreach ($headers as $ci => $h) {
            $col        = xlsx_col_letter($ci + 1);
            $v          = isset($row[$h]) ? (string)$row[$h] : '';
            $v          = htmlspecialchars($v, ENT_XML1, 'UTF-8');
            $sheet_rows .= '<c r="' . $col . $rn . '" t="inlineStr"><is><t>' . $v . '</t></is></c>';
        }
        $sheet_rows .= '</row>';
    }
 
    $files = [];
 
    $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"          ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>';
 
    $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
 
    $files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets><sheet name="Export" sheetId="1" r:id="rId1"/></sheets>
</workbook>';
 
    $files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>';
 
    $files['xl/worksheets/sheet1.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>' . $sheet_rows . '</sheetData>
</worksheet>';
 
    $zip_path = sys_get_temp_dir() . '/' . $name . '.xlsx';
    $zip      = new ZipArchive();
 
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        wp_die('Error creating XLSX file.');
    }
    foreach ($files as $path => $content) {
        $zip->addFromString($path, $content);
    }
    $zip->close();
 
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');
    header('Content-Length: ' . filesize($zip_path));
    header('Pragma: no-cache');
    readfile($zip_path);
    unlink($zip_path);
}
 
function xlsx_col_letter($n) {
    $letter = '';
    while ($n > 0) {
        $rem    = ($n - 1) % 26;
        $letter = chr(65 + $rem) . $letter;
        $n      = (int)(($n - 1) / 26);
    }
    return $letter;
}
