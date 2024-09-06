<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;

class Version20230222123043 extends Version
{
    protected $description = "Переиндексация поиска";

    protected $moduleVersion = "4.2.4";

    public function up()
    {
        Loader::includeModule('search');

        $time_start = time();
        $progress = [];
        $max_execution_time = 100000; // все элементы индексируются только при большом шаге

        while (is_array($progress)) {
            $progress = \CSearch::ReIndexAll(true, $max_execution_time, $progress);
        }

        $total_time = time() - $time_start;
        echo 'reindex finished. total time: ' . $total_time . ' seconds, indexed elements: ' . $progress . "\r\n";
    }

    public function down()
    {
        //your code ...
    }
}
