<?php

namespace Cube\Controllers;

use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Cube\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag('subscribe')]
class Subscribe extends BaseController
{
    /**
     * Подписка на рассылку
     *
     * @param string $email
     * @return boolean|null
     */
    #[OA\Post(path: '/api/v1/subscribe', tags: ['subscribe'])]
    #[OA\QueryParameter(name: 'email')]
    #[OA\Response(response: 200, description: 'Успешная подписка')]
    #[OA\Response(response: 400, description: 'Ошибка в переданных данных')]
    #[OA\Response(response: 409, description: 'Пользователь с такой почтой уже найден в системе')]
    public function subscribeAction(string $email = ''): ?bool
    {
        if (check_email($email)) {
            // Проверяем наличие подписки
            $contactSubscribe = \Bitrix\Sender\ContactTable::getList(['filter' => ['CODE' => $email]])->fetchObject();

            if ($contactSubscribe == null) {
                // Добавляем контакт без определенной подписки
                $result = \Bitrix\Sender\Subscription::subscribe(['EMAIL' => $email, 'SUBSCRIBE_LIST' => []]);
            } else {
                Context::getCurrent()->getResponse()->setStatus(409);
                $this->addError(new Error('Вы уже подписаны на рассылку')); // Отдаем ошибку
                return null;
            }
        } else {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Ошибка при валидации email')); // Отдаем ошибку
            return null;
        }
        return (bool) $result;
    }
}
