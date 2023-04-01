<?php
require_once './vendor/autoload.php';
use Spatie\SimpleExcel\SimpleExcelWriter;

function downLoad($file,$data) {
    $writer = SimpleExcelWriter::streamDownload($file);
        foreach ($data as $k => $val) {
            $writer->addRow([
                'PID' => $val['pid'],
                'Username' => $val['member_name'],
                'Address' => $val['address'],
                'Amount' => $val['amount'],
                'state' => $val['state'],
                'complete' => $val['complete'],
                'Time' => date('Y-m-d H:i:s',$val['create_at']),
            ]);
            if ($k % 1000 === 0) {
                flush(); // Flush the buffer every 1000 rows
            }
        }
       $writer ->toBrowser();
}
