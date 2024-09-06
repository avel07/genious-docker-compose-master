<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;
use OpenApi\Attributes as OA;

#[OA\Tag('blog')]
class Blog extends BaseController
{
    public const DEFAULT_SIZE_PAGE = 6;

    /**
     * Получение корневой страницы блога
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/blog', tags: ['blog'])]
    #[OA\QueryParameter(name: 'page', description: 'Страница пагинации', example: 1)]
    #[OA\QueryParameter(name: 'size', description: 'Кол-во элементов на страницу', example: self::DEFAULT_SIZE_PAGE)]
    #[OA\Response(response: 200, description: 'Блог')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(int $page = 1, int $size = self::DEFAULT_SIZE_PAGE, PageNavigation $pageNavigation = null): ?array
    {
        $result = [
            'items' => [],
            'page'  => $page,
            'size'  => $size,
            'count' => 0,
            'total' => 0
        ];

        if (Loader::includeModule('iblock')) {
            // Pagintaion
            if ($pageNavigation && $pageNavigation instanceof PageNavigation) {
                $pageNavigation->setPageSize($size);
                $pageNavigation->setCurrentPage($page);
            }

            // prepare limit
            $qLimit = $pageNavigation ? $pageNavigation->getLimit() : \Bitrix\Iblock\Controller\DefaultElement::DEFAULT_LIMIT;
            $qOffset = $pageNavigation ? $pageNavigation->getOffset() : 0;

            // Получаем объекты элемента
            $blogQuery = \Bitrix\Iblock\Elements\ElementBlogTable::query()
                ->setOrder(['ACTIVE_FROM' => 'DESC'])
                ->where('ACTIVE', true)
                ->addSelect('ID')
                ->addSelect('IBLOCK.ID')
                ->addSelect('CODE')
                ->addSelect('NAME')
                ->addSelect('PREVIEW_PICTURE')
                ->addSelect('ACTIVE_FROM')
                ->setLimit($qLimit)
                ->setOffset($qOffset);

            $resultQuery = $blogQuery->exec();

            // count total records
            $countTotal = $blogQuery->queryCountTotal();

            $elementCollection = $blogQuery->fetchCollection();

            // Если коллекцию не получили
            if (!$elementCollection) {
                Context::getCurrent()->getResponse()->setStatus(500);
                $this->addError(new Error('Collection error'));
                return null;
            }

            $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
            foreach ($elementCollection as $elementObject) {
                /** @var \Bitrix\Iblock\ORM\ValueStorageEntity */
                $item = [];
                $item['name'] = $elementObject->get('NAME');
                $item['code'] = $elementObject->get('CODE');
                $item['image'] = $basePath . \CFile::GetPath($elementObject->get('PREVIEW_PICTURE'));

                $result['items'][] = $item;
            }

            $result['count'] = count($result['items']);
            $result['total'] = (int) $countTotal;

            return $result;
        }
        return [];
    }

    /**
     * Получение детальной страницы Блога
     *
     * @param string $code
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/blog/{code}', tags: ['blog'])]
    #[OA\PathParameter(name: 'code')]
    #[OA\Response(response: 200, description: 'Статья')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    #[OA\Response(response: 'default', description: 'Error')]
    public function getAction(string $code): ?array
    {
        $result = [];
        if (Loader::includeModule('iblock')) {

            // cache
            $cache = \Bitrix\Main\Application::getInstance()->getCache();
            $cacheId = 'api_blog_page_' . $code;     // Уникальный идентификатор
            $cachePath = '/rest/api_blog_page';      // Путь к кешу
            $cacheTtl = 3600;

            if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
                $result = $cache->getVars(); // достаем переменные из кеша
            } else {
                $itemObject = \Bitrix\Iblock\Elements\ElementBlogTable::query()
                    ->where('CODE', $code)
                    ->where('ACTIVE', true)
                    ->addSelect('ID')
                    ->addSelect('IBLOCK.ID')
                    ->addSelect('CODE')
                    ->addSelect('NAME')
                    ->addSelect('DETAIL_TEXT')
                    ->addSelect('ACTIVE_FROM')
                    ->setLimit(1)
                    ->fetchObject();

                if (!$itemObject) {
                    Context::getCurrent()->getResponse()->setStatus(404);
                    $this->addError(new Error('Страница не найдена', 'NOT_FOUND'));
                    return null;
                }

                $prevObject = \Bitrix\Iblock\Elements\ElementBlogTable::query()
                    ->setOrder(['ACTIVE_FROM' => 'DESC'])
                    ->where('ACTIVE_FROM', '<', $itemObject['ACTIVE_FROM'])
                    ->where('ACTIVE', true)
                    ->addSelect('ID')
                    ->addSelect('IBLOCK.ID')
                    ->addSelect('CODE')
                    ->addSelect('NAME')
                    ->addSelect('PREVIEW_PICTURE')
                    ->setLimit(1)
                    ->fetchObject();

                $nextObject = \Bitrix\Iblock\Elements\ElementBlogTable::query()
                    ->setOrder(['ACTIVE_FROM' => 'ASC'])
                    ->where('ACTIVE_FROM', '>', $itemObject['ACTIVE_FROM'])
                    ->where('ACTIVE', true)
                    ->addSelect('ID')
                    ->addSelect('IBLOCK.ID')
                    ->addSelect('CODE')
                    ->addSelect('NAME')
                    ->addSelect('PREVIEW_PICTURE')
                    ->setLimit(1)
                    ->fetchObject();

                $result['name'] = $itemObject->get('NAME');
                $result['code'] = $itemObject->get('CODE');
                $result['text'] = $itemObject->get('DETAIL_TEXT');

                // Получаем SEO параметры
                if ($itemObject->get('IBLOCK') && $itemObject->get('IBLOCK')->get('ID')) {
                    $ipropElementValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($itemObject->get('IBLOCK')->get('ID'), $itemObject->get('ID'));
                    $seoFields = $ipropElementValues->getValues();
                }

                $result['meta'] = [
                    'metaTitle' => $seoFields['ELEMENT_META_TITLE'] ?? null,
                    'metaDescription' => $seoFields['ELEMENT_META_DESCRIPTION'] ?? null,
                    'metaPageTitle' => $seoFields['ELEMENT_PAGE_TITLE'] ?? $itemObject->get('NAME'),
                ];

                $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();

                // Предыдущий элемент блога
                if (!$prevObject || !$prevObject->get('PREVIEW_PICTURE')) {
                    $result['previous'] = [];
                } else {
                    $result['previous']['image'] = $basePath . \CFile::GetPath($prevObject->get('PREVIEW_PICTURE'));
                    $result['previous']['name'] = $prevObject->get('NAME');
                    $result['previous']['code'] = $prevObject->get('CODE');
                }

                // Следующий элемент блога
                if (!$nextObject || !$nextObject->get('PREVIEW_PICTURE')) {
                    $result['next'] = [];
                } else {
                    $result['next']['image'] = $basePath . \CFile::GetPath($nextObject->get('PREVIEW_PICTURE'));
                    $result['next']['name'] = $nextObject->get('NAME');
                    $result['next']['code'] = $nextObject->get('CODE');
                }

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
