<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use OpenApi\Attributes as OA;

#[OA\Tag('content')]
class Content extends BaseController
{
    /**
     * Получение контент-страницы
     *
     * @param string $pageCode
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/content/{pageCode}', tags: ['content'])]
    #[OA\PathParameter(name: 'pageCode')]
    #[OA\Response(response: 200, description: 'Контент-страница')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    #[OA\Response(response: 'default', description: 'Error')]
    public function getAction(string $pageCode): ?array
    {
        $result = [];
        if (Loader::includeModule('iblock')) {
            // cache
            $cache = \Bitrix\Main\Application::getInstance()->getCache();
            $cacheId = 'api_content_page_' . $pageCode;     // Уникальный идентификатор
            $cachePath = '/rest/api_content_page';          // Путь к кешу
            $cacheTtl = 3600;

            if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
                $result = $cache->getVars(); // достаем переменные из кеша
            } else {
                $itemObject = \Bitrix\Iblock\Elements\ElementContentTable::query()
                    ->where('CODE', $pageCode)
                    ->where('ACTIVE', true)
                    ->addSelect('ID')
                    ->addSelect('IBLOCK.ID')
                    ->addSelect('CODE')
                    ->addSelect('NAME')
                    ->addSelect('NAME')
                    ->addSelect('image.DESCRIPTION')
                    ->addSelect('image.FILE')
                    ->addSelect('text')
                    ->fetchObject();

                if (!$itemObject) {
                    Context::getCurrent()->getResponse()->setStatus(404);
                    $this->addError(new Error('Страница не найдена', 'NOT_FOUND'));
                    return null;
                }
                
                $result['name'] = $itemObject->get('NAME');
                $result['code'] = $itemObject->get('CODE');

                // Получаем тексты
                $result['text'] = [];
                if ($itemObject->get('text')) {
                    foreach ($itemObject->get('text') as $textObject) {
                        $code = !empty($textObject->get('DESCRIPTION')) ? $textObject->get('DESCRIPTION') : null;
                        $result['text'][$code] = $textObject->get('VALUE');;
                    }
                }

                $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
                // Получаем изображения
                $result['image'] = [];
                if ($itemObject->get('image')) {
                    foreach ($itemObject->get('image') as $imageObject) {
                        $imageFileObject = $imageObject->get('FILE');
                        $code = !empty($imageObject->get('DESCRIPTION')) ? $imageObject->get('DESCRIPTION') : null;
                        $result['image'][$code] = [
                            'height' => $imageFileObject->get('HEIGHT'),
                            'width'  => $imageFileObject->get('WIDTH'),
                            'type'   => $imageFileObject->get('CONTENT_TYPE'),
                            'src'    => $basePath . \CFile::GetFileSRC($imageFileObject->collectValues())
                        ];
                    }
                }

                // Получаем SEO параметры
                if ($itemObject->get('IBLOCK') && $itemObject->get('IBLOCK')->get('ID')) {
                    $ipropElementValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($itemObject->get('IBLOCK')->get('ID'), $itemObject->get('ID'));
                    $seoFields = $ipropElementValues->getValues();
                }

                $result['meta'] = [
                    'metaTitle'       => $seoFields['ELEMENT_META_TITLE'] ?? null,
                    'metaDescription' => $seoFields['ELEMENT_META_DESCRIPTION'] ?? null,
                ];

                // Начинаем писать в кеш
                if ($cache->startDataCache()) {
                    // Тегированный кеш (обновляется при обновлении элемента в бд)
                    $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                    $taggedCache->startTagCache($cachePath);                                            // Путь к основному кешу (relativePath)
                    $taggedCache->registerTag('iblock_id_' . $itemObject->get('IBLOCK')->get('ID'));    // Тег инфоблока (триггер)
                    $taggedCache->endTagCache();
                    // Записываем в кеш результат
                    $cache->endDataCache($result);
                }
            }

            return $result;
        }
        return [];
    }
}
