<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use \Bitrix\Main\Web\Json;
use OpenApi\Attributes as OA;

#[OA\Tag('content')]
class Collection extends BaseController
{
    /**
     * Получение коллекции
     *
     * @param string $pageCode
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/collection/{pageCode}', tags: ['content'])]
    #[OA\PathParameter(name: 'pageCode')]
    #[OA\Response(response: 200, description: 'Коллекция')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    #[OA\Response(response: 'default', description: 'Error')]
    public function getAction(string $pageCode): ?array
    {
        Loader::includeModule("iblock"); 

        // cache
        $cache = \Bitrix\Main\Application::getInstance()->getCache();
        $cacheId = 'api_collection' . $pageCode;     // Уникальный идентификатор
        $cachePath = '/rest/api_collection';      // Путь к кешу
        $cacheTtl = 3600;

        if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
            $result = $cache->getVars(); // достаем переменные из кеша
        } else {
            $item = \Bitrix\Iblock\Elements\ElementCollectionTable::query()
                ->where('CODE', $pageCode)
                ->addSelect('COLLECTION')
                ->addSelect('IBLOCK.ID')
                ->fetchObject();

            $collectionJson = $item->get('COLLECTION')?->get('VALUE');
            $collection = Json::decode($collectionJson);

            $layouts = $collection['layouts'];
            $blocks = $collection['blocks'];

            // Меняем структуру слоев
            foreach ($layouts as &$layout) {
                $layout['settings'] = explode(" ", (string) $layout['columns'][0]['css']);
                unset($layout['columns']);
            }
            unset($layout);

            // Проставляем обсолютные ссылки
            $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
            array_walk_recursive($blocks, function (&$value, $key) use ($basePath) {
                $rest[] = $key;
                if ($key == 'SRC' || $key == 'ORIGIN_SRC') {
                    $value = $basePath . $value;
                }
            });

            foreach ($blocks as $block) {
                $layoutKey = mb_substr($block['layout'], 0, 1);
                unset($block['layout']);
                $layouts[$layoutKey]['blocks'][] = $block;
            }

            $result = $layouts;
            // Начинаем писать в кеш
            if ($cache->startDataCache()) {
                // Тегированный кеш (обновляется при обновлении элемента в бд)
                $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache($cachePath);                                            // Путь к основному кешу (relativePath)
                $taggedCache->registerTag('iblock_id_' . $item->get('IBLOCK')->get('ID'));    // Тег инфоблока (триггер)
                $taggedCache->endTagCache();
                // Записываем в кеш результат
                $cache->endDataCache($result);
            }
        }

        return $result;
    }
}