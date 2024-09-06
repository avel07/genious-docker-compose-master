<?php

namespace Sprint\Migration;


class Version20230214090610 extends Version
{
    protected $description   = "Highload цветов - с HEX - элементы";
    protected $moduleVersion = "4.2.4";

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $this->getExchangeManager()
             ->HlblockElementsImport()
             ->setExchangeResource('hlblock_elements.xml')
             ->setLimit(20)
             ->execute(function ($item) {
                 $this->getHelperManager()
                      ->Hlblock()
                      ->addElement(
                          $item['hlblock_id'],
                          $item['fields']
                      );
             });
    }

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function down()
    {
    }


}
