<?php

namespace Sprint\Migration;


class Version20230309091716 extends Version
{
    protected $description = "Удаление инфоблока псевдо-категорий";

    protected $moduleVersion = "4.2.4";

    public function up()
    {
        $helper = $this->getHelperManager();
        $ok = $helper->Iblock()->deleteIblockIfExists('category');

        if ($ok) {
            $this->outSuccess('Инфоблок "category" удален');
        } else {
            $this->outSuccess('Ошибка удаления инфоблока. Инфоблока не существует');
        }
    }

    public function down()
    {
        //your code ...
    }
}
