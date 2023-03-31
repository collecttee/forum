<?php
require_once './vendor/autoload.php';
use Spatie\SimpleExcel\SimpleExcelWriter;

function downLoad($file,$data) {
    SimpleExcelWriter::streamDownload('your-export.xlsx')
        ->addRow([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ])
        ->addRow([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ])
        ->toBrowser();
}
