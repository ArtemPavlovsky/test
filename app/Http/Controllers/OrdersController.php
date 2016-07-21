<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use SoapBox\Formatter\Formatter;

class OrdersController extends Controller
{
    public function testReader()
    {
        $csv = \File::get('files/csv.csv');
        /*$csv = str_replace("\t", ';;;', $csv);
        $csv = str_replace(",", '\t', $csv);
        $csv = str_replace(";;;", ',', $csv);*/
        $formatter = Formatter::make($csv, Formatter::CSV);
        dd($formatter->toArray());
//        $xml = \File::get('files/xml.xml');
//        $formatter = Formatter::make($xml, Formatter::XML);
//        dd($formatter->toArray()['stat'][0]['currency']);
    }

    public function initCron()
    {
        $output = shell_exec('crontab -l');
        $artisan_path = substr(app_path(), 0, -3).'artisan';
        file_put_contents('crontab.txt', $output.'* * * * * php '.$artisan_path.' schedule:run >> /dev/null 2>&1'.PHP_EOL);
        echo exec('crontab crontab.txt');
        return $artisan_path.' --TASK ADDED';
    }

    public function deinitCron()
    {
        echo exec('crontab -r');
        return 'crontab -r';
    }
}
