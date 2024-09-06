<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use OpenApi\Attributes as OA;

#[OA\Tag('faq')]
class Faq extends BaseController
{
    /**
     * Получение страницы вопросов и ответов
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/faq', tags: ['faq'])]
    #[OA\Response(response: 200, description: 'Вопросы и ответы')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function getAction(): ?array
    {
        $result = [
            'items' => []
        ];

        if (Loader::includeModule('iblock')) {
            // Получаем объекты элемента
            $elementCollection = \Bitrix\Iblock\Elements\ElementFaqTable::query()
                ->setOrder(['SORT' => 'ASC'])
                ->where('ACTIVE', true)
                ->addSelect('ID')
                ->addSelect('NAME')
                ->addSelect('PREVIEW_TEXT')
                ->fetchCollection();

            if (!$elementCollection) {
                Context::getCurrent()->getResponse()->setStatus(404);
                $this->addError(new Error('Страница не найдена', 'NOT_FOUND'));
                return null;
            }

            foreach ($elementCollection as $elementObject) {
                /** @var \Bitrix\Iblock\ORM\ValueStorageEntity */
                $item = [];
                $item['name'] = $elementObject->get('NAME');
                $item['id'] = $elementObject->get('ID');
                $item['text'] = $elementObject->get('PREVIEW_TEXT');

                $result['items'][] = $item;
            }

            return $result;
        }
        return [];
    }
}
