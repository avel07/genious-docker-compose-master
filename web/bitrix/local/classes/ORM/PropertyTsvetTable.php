<?php

namespace Cube\ORM;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

class PropertyTsvetTable extends DataManager
{
    /**
     * Цвета
     *
     * @var array
     */
    public static $colors = [];

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_tsvet';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true
                ]
            ),
            new TextField('UF_NAME'),
            new TextField('UF_NAME_ENG'),
            new TextField('UF_XML_ID'),
            new TextField('UF_HEX'),
            new TextField('UF_LINK'),
            new TextField('UF_DESCRIPTION'),
            new TextField('UF_FULL_DESCRIPTION'),
            new IntegerField('UF_SORT'),
            new IntegerField('UF_FILE'),
            new IntegerField('UF_DEF'),
        ];
    }

    /**
     * Получить цвет по XML_ID
     *
     * @param string $xmlId
     * @return void
     */
    public static function getColorByXmlId(string $xmlId):? array
    {
        if (empty(static::$colors)) {
            $cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
            $cacheId = 'b_tsvet_values';
            $cacheTtl = 7200;
            if ($cache->read($cacheTtl, $cacheId)) {
                static::$colors = $cache->get($cacheId); // достаем переменные из кеша
            } else {
                $colorValues = static::query()
                    ->whereNotNull('UF_HEX')
                    ->addSelect('UF_XML_ID')
                    ->addSelect('UF_HEX')
                    ->addSelect('UF_NAME')
                    ->addSelect('UF_SORT')
                    ->fetchCollection();

                // $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
                foreach ($colorValues as $color) {
                    static::$colors[$color->getUfXmlId()] = [
                        'SORT'   => $color->get('UF_SORT'),
                        'XML_ID' => $color->get('UF_XML_ID'),
                        'NAME'   => $color->get('UF_NAME'),
                        'HEX'    => $color->get('UF_HEX'),
                        // 'SRC'    => $color->getUfFile() ? $basePath . \CFile::GetPath($color->getUfFile()) : null,
                    ];
                }

                $cache->set($cacheId, static::$colors); // записываем в кеш
            }
        }
        return isset(static::$colors[$xmlId]) ? static::$colors[$xmlId] : null;
    }
}
