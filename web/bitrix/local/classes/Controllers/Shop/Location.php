<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;

class Location extends BaseController
{
    public const COUNTRY_CODE_RU = '0000028023';
    public const COUNTRY_CODE_UA = '0000000364';
    public const COUNTRY_CODE_KZ = '0000000276';
    public const COUNTRY_CODE_BY = '0000000001';

    /**
     * Получение города нужных стран
     *
     * @return array|null
     */
    public function getCitiesAction(string $code = self::COUNTRY_CODE_RU): ?array
    {
        \Bitrix\Main\Loader::includeModule('sale');

        $result = [];

        // Разрешенные страны для получения городов
        $allowCountries = [
            self::COUNTRY_CODE_RU,
            self::COUNTRY_CODE_UA,
            self::COUNTRY_CODE_KZ,
            self::COUNTRY_CODE_BY
        ];

        if (!in_array($code, $allowCountries)) {
            return null;
        }

        $langCode = 'ru';

        $locations = \Bitrix\Sale\Location\LocationTable::query()
            ->where('TYPE.CODE', 'CITY')
            ->where('NAME.LANGUAGE_ID', $langCode)
            ->where('PARENT.NAME.LANGUAGE_ID', $langCode)
            ->where('PARENTS.NAME.LANGUAGE_ID', $langCode)
            ->where('PARENTS.TYPE.CODE', 'COUNTRY')
            ->where('PARENTS.CODE', $code)
            ->addSelect('ID')
            ->addSelect('CODE')
            ->addSelect('NAME.NAME')
            ->addSelect('PARENT.NAME')
            ->addSelect('PARENT.TYPE.CODE')
            ->addSelect('PARENTS.NAME')
            ->setOrder([
                'SORT' => 'asc',
                'ID'   => 'asc'
            ])
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->fetchCollection();

        if ($locations !== null && $locations->count() > 0) {
            foreach ($locations as $location) {
                $parentType = $location->get('PARENT')->get('TYPE')->get('CODE');

                // Верхние уровни
                $parents = [];
                if ($parentType !== 'COUNTRY') {
                    $parents[] = $location->get('PARENT')->get('NAME')->get('NAME');
                }
                // Страна
                $parents[] = $location->get('PARENTS')->get('NAME')->get('NAME');

                $result[] = [
                    'code' => $location->get('CODE'),
                    'name' => $location->get('NAME')->get('NAME'),
                    'parent' => implode(', ', $parents)
                ];
            }
        } else {
            $this->addError(new \Bitrix\Main\Error('Ошибка при получении местоположений', "ERR_RESULT"));
            return null;
        }

        return $result;
    }

    /**
     * Получить страны
     *
     * @return array|null
     */
    public function getCountiesAction(): ?array
    {
        \Bitrix\Main\Loader::includeModule('sale');

        $result = [];
        $langCode = 'ru';

        $locations = \Bitrix\Sale\Location\LocationTable::query()
            ->where('TYPE.CODE', 'COUNTRY')
            ->where('NAME.LANGUAGE_ID', $langCode)
            ->addSelect('ID')
            ->addSelect('CODE')
            ->addSelect('NAME.NAME')
            ->setOrder([
                'SORT' => 'asc',
            ])
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->fetchCollection();

        if ($locations !== null && $locations->count() > 0) {
            foreach ($locations as $location) {
                $result[] = [
                    'code' => $location->get('CODE'),
                    'name' => $location->get('NAME')->get('NAME'),
                ];
            }
        } else {
            throw new \Bitrix\Main\SystemException("Ошибка при получении местоположений");
        }

        return $result;
    }
}
