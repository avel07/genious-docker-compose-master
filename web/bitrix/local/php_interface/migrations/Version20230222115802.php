<?php

namespace Sprint\Migration;


class Version20230222115802 extends Version
{
    protected $description = "Фасетные индексы";

    protected $moduleVersion = "4.2.4";

    public function up()
    {
        // Создать фасетный индекс для инфоблока
        if(\Bitrix\Main\Loader::includeModule('iblock')){
            \Bitrix\Iblock\PropertyIndex\Manager::DeleteIndex(1);
            \Bitrix\Iblock\PropertyIndex\Manager::markAsInvalid(1);
            $index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer(1);
            $index->startIndex();
            $index->continueIndex();
            $index->endIndex();
            \Bitrix\Iblock\PropertyIndex\Manager::checkAdminNotification();
            \CBitrixComponent::clearComponentCache("bitrix:catalog.smart.filter");
            \CIBlock::clearIblockTagCache(1);
        }
    }

    public function down()
    {
        //your code ...
    }
}
