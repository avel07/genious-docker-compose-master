<?php

use Cube\Controllers\Shop\Catalog;

$settings = [
    'title' => 'Коллекции',

    // Настройки блоков
    'block_settings' => [
        'iblock_elements' => [
            'enabled_iblocks' => [
                'type'  => 'hidden',
                'value' => [
                    Catalog::CATLOG_IBLOCK_ID
                ],
            ],
            'myparam1' => [
                'type' => 'select',
                'value' => [
                    'default'     => 'Стандартные',
                    'slider'      => 'Слайдер',
                    'big_picture' => 'Большие карточки',
                ]
            ],
        ],
    ],

    //Разрешить добавление указанных блоков
    'block_enabled'   => [
        'complex_image_text',
        'text',
        'gallery',
        'iblock_elements'
    ],

    // Параметры сетки
    'layout_classes' => [
        'type1' => [
            // ['m-hidden', ],
            // [
            //     'm-show',
            //     't-show',
            // ],
            [
                'isBanner',
            ]
        ],
    ],

    //Названия классов для колонок
    'layout_titles'   => [
        'm-hidden' => 'Скрыть для мобильных устройств',
        'm-show' => 'Только для мобильных',
        't-show' => 'Только для мобильных и планшетов',
        'isBanner' => 'Главный баннер',
    ],

    // Доступна только первая сетка
    'layout_enabled'  => [
        'layout_1',
    ],
];
