<?php

namespace Cube\Controllers;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Type\Date;
use OpenApi\Attributes as OA;

#[OA\Tag('user')]
class User extends BaseController
{
    /**
     * Регистрация пользователя
     *
     * @param string $name
     * @param string $lastName
     * @param string $date
     * @param string $phone
     * @param string $email
     * @param string $password
     * @param string $passwordRepeat
     * @return void|array
     */
    #[OA\Post(path: '/api/v1/user/register', tags: ['user'])]
    #[OA\QueryParameter(name: 'name')]
    #[OA\QueryParameter(name: 'lastName')]
    #[OA\QueryParameter(name: 'date')]
    #[OA\QueryParameter(name: 'phone')]
    #[OA\QueryParameter(name: 'email')]
    #[OA\QueryParameter(name: 'password')]
    #[OA\QueryParameter(name: 'passwordRepeat')]
    #[OA\Response(response: 201, description: 'Пользователь успешно зарегистрирован')]
    #[OA\Response(response: 400, description: 'Ошибка в переданных данных')]
    #[OA\Response(response: 409, description: 'Пользователь с такой почтой уже найден в системе')]
    public function registerAction($name, $lastName = '', $date = '', $phone, $email, $password, $passwordRepeat)
    {
        if(preg_match('/[\d]/s', $name, $matches) || preg_match('/[\d]/s', $lastName, $matches)) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Имя и фамилия не может содержать цифр', 'PASSWORD_SHORT'));
            return null;
        }

        if (strlen($password) < 6) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Пароль должен быть не короче 6 символов', 'PASSWORD_SHORT'));
            return null;
        }

        if ($password !== $passwordRepeat) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Повтор пароля не совпадает с паролем', 'PASSWORD_DIFFERENT'));
            return null;
        }

        $userCount = \Bitrix\Main\UserTable::getList([
            'filter' => ['EMAIL' => $email],
            'limit' => 1,
            'count_total' => true,
            'select' => ['ID']
        ])->getCount();
        if ($userCount > 0) {
            Context::getCurrent()->getResponse()->setStatus(409);
            $this->addError(new Error('Пользователь с такой почтой уже найден в системе', 'USER_EXIST'));
            return null;
        }

        $user = new \CUser();
        $fields = [
            'ACTIVE' => 'Y',
            'NAME' => $name,
            'LAST_NAME' => $lastName,
            'EMAIL' => $email,
            'UF_NOTIFICATION_EMAIL' => $email,
            'LOGIN' => $email,
            'PERSONAL_PHONE' => $phone,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $passwordRepeat,
        ];
        if (!empty($date)) {
            $fields['PERSONAL_BIRTHDAY'] = new Date($date, 'Y-m-d');
        }

        $userId = $user->Add($fields);
        if (intval($userId) > 0) {
            //TODO: Generate token and authorize
            Context::getCurrent()->getResponse()->setStatus(201);
            /** @var \CUser $USER */
            global $USER;
            $USER->Authorize($userId);
            return $this->forward($this, 'get');
        } else {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error($user->LAST_ERROR, 'USER_NOT_CREATED'));
            return null;
        }
    }

    /**
     * Авторизация пользователя
     *
     * @param string $email
     * @param string $password
     * @return void|array
     */
    #[OA\Post(path: '/api/v1/user/login', tags: ['user'])]
    #[OA\QueryParameter(name: 'email')]
    #[OA\QueryParameter(name: 'password')]
    #[OA\Response(response: 200, description: 'Пользователь успешно авторизован')]
    #[OA\Response(response: 400, description: 'Неверные данные')]
    #[OA\Response(response: 401, description: 'Пользователь с указанными данными не найден')]
    public function loginAction(string $email, $password)
    {
        if (!check_email($email)) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Email неврный', 'EMAIL_FAILED'));
            return null;
        }

        $userObject = \Bitrix\Main\UserTable::query()
            ->where('EMAIL', $email)
            ->setLimit(1)
            ->addSelect('ID')
            ->addSelect('LOGIN')
            ->fetchObject();

        $login = $userObject ? $userObject->get('LOGIN') : $email;

        $arParams = [
            "LOGIN"             => &$login,
            "PASSWORD"          => &$password,
            "REMEMBER"          => 'N',
            "PASSWORD_ORIGINAL" => 'Y',
        ];
        $messageError = null;

        /** @var \CUser $USER */
        global $USER;
        $userId = $USER->LoginInternal($arParams, $messageError);

        if (!empty($messageError['MESSAGE']) || $userId != $userObject->get('ID')) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error(strip_tags($messageError['MESSAGE']), 'LOGIN_FAILED'));
            return null;
        }

        $USER->Authorize($userId);
        return $this->forward($this, 'get');
    }

    /**
     * Получить пользователя
     *
     * @param string $id
     * @return void|array
     */
    #[OA\Get(path: '/api/v1/user', tags: ['user'])]
    #[OA\Response(response: 200, description: 'Пользователь найден')]
    #[OA\Response(response: 401, description: 'Пользователь не авторизован')]
    #[OA\Response(response: 404, description: 'Пользователь не найден')]
    public function getAction()
    {
        $userId = CurrentUser::get()?->getId();
        if (!$userId) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error('Пользователь не авторизован', 'USER_NOT_AUTH'));
            return null;
        }

        $user = \Bitrix\Main\UserTable::getList([
            'filter' => ['=ID' => $userId],
            'limit' => 1,
            'select' => [
                'ID', 'NAME', 'LAST_NAME', 'PERSONAL_PHONE', 'EMAIL', 'GROUPS', 'PERSONAL_BIRTHDAY',
                'UF_LETTERS_DISCOUNT', 'UF_LETTERS_WISHLIST', 'UF_LETTERS_STATUS', 'UF_NOTIFICATION_EMAIL'
            ]
        ])->fetchObject();

        if (empty($user)) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Пользователь не найден', 'USER_NOT_FOUND'));
            return null;
        }

        $arGroups = [];
        $groups = $user->getGroups()->getAll();
        foreach ($groups as $group) {
            $arGroups[] = $group->getGroupId();
        }

        $user = $user->collectValues();
        $result = [
            'ID'                    => $user['ID'],
            'NAME'                  => $user['NAME'],
            'LAST_NAME'             => $user['LAST_NAME'],
            'PERSONAL_PHONE'        => $user['PERSONAL_PHONE'],
            'EMAIL'                 => $user['EMAIL'],
            'UF_NOTIFICATION_EMAIL' => $user['UF_NOTIFICATION_EMAIL'],
            'UF_LETTERS_DISCOUNT'   => $user['UF_LETTERS_DISCOUNT'] > 0,
            'UF_LETTERS_WISHLIST'   => $user['UF_LETTERS_WISHLIST'] > 0,
            'UF_LETTERS_STATUS'     => $user['UF_LETTERS_STATUS'] > 0,
            'GROUPS'                => $arGroups
        ];
        $result['PERSONAL_BIRTHDAY'] = $user['PERSONAL_BIRTHDAY']?->format('Y-m-d');

        Context::getCurrent()->getResponse()->setStatus(200);
        return $result;
    }

    /**
     * Обновить поля пользователя
     *
     * @param string $id
     * @return void|bool
     */
    #[OA\Post(path: '/api/v1/user', tags: ['user'])]
    #[OA\QueryParameter(name: 'NAME', description: 'Имя пользователя', required: true)]
    #[OA\QueryParameter(name: 'LAST_NAME', description: 'Фамилия пользователя', required: true)]
    #[OA\QueryParameter(name: 'PERSONAL_PHONE', description: 'Телефон', required: true)]
    #[OA\QueryParameter(name: 'EMAIL', description: 'Email', required: true)]
    #[OA\QueryParameter(name: 'UF_LETTERS_DISCOUNT', description: 'Рассылки о скидках и акциях', required: false)]
    #[OA\QueryParameter(name: 'UF_LETTERS_WISHLIST', description: 'Уведомления о вишлисте', required: false)]
    #[OA\QueryParameter(name: 'UF_LETTERS_STATUS', description: 'Уведомления о статусах заказов', required: false)]
    #[OA\QueryParameter(name: 'UF_NOTIFICATION_EMAIL', description: 'Почта для уведомлений', required: false)]
    #[OA\QueryParameter(name: 'PASSWORD', description: 'Почта для уведомлений', required: false)]
    #[OA\Response(response: 200, description: 'Пользователь обновлен')]
    #[OA\Response(response: 400, description: 'Неверные данные')]
    #[OA\Response(response: 404, description: 'Пользователь не найден')]
    public function updateAction()
    {
        $userId = CurrentUser::get()?->getId();
        if (!$userId) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Пользователь не найден', 'USER_NOT_FOUND'));
            return null;
        }

        $arUser = \Bitrix\Main\UserTable::getByPrimary($userId, [
            'select' => [
                'ID'
            ]
        ])->fetch();

        if (empty($arUser)) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Пользователь не найден', 'USER_NOT_FOUND'));
            return null;
        }

        $params = Context::getCurrent()->getRequest()->getValues();
        if (empty($params['NAME']) || empty($params['LAST_NAME']) || empty($params['PERSONAL_PHONE']) || empty($params['EMAIL'])) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Заполните обязательные поля', 'REQUIRED_FIELDS'));
            return null;
        }

        $fields = [
            'NAME'                  => $params['NAME'],
            'LAST_NAME'             => $params['LAST_NAME'],
            'PERSONAL_PHONE'        => $params['PERSONAL_PHONE'],
            'EMAIL'                 => $params['EMAIL'],
            'UF_NOTIFICATION_EMAIL' => $params['UF_NOTIFICATION_EMAIL'] ?? '',
            'UF_LETTERS_DISCOUNT'   => !empty($params['UF_LETTERS_DISCOUNT']) ? 1 : 0,
            'UF_LETTERS_WISHLIST'   => !empty($params['UF_LETTERS_WISHLIST']) ? 1 : 0,
            'UF_LETTERS_STATUS'     => !empty($params['UF_LETTERS_STATUS']) ? 1 : 0
        ];
        if (!empty($params['PERSONAL_BIRTHDAY'])) {
            $fields['PERSONAL_BIRTHDAY'] = new Date($params['PERSONAL_BIRTHDAY'], 'Y-m-d');
        }
        if (!empty($params['PASSWORD'])) {
            if (strlen($params['PASSWORD']) < 6) {
                Context::getCurrent()->getResponse()->setStatus(400);
                $this->addError(new Error('Пароль должен быть не короче 6 символов', 'PASSWORD_SHORT'));
                return null;
            }
            $fields['PASSWORD'] = $params['PASSWORD'];
        }

        $user = new \CUser();
        if ($user->Update($userId, $fields)) {
            return $this->forward($this, 'get');
        } else {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Не получилось обновить пользователя', 'UPDATE_ERROR'));
            return null;
        }
    }

    /**
     * Выход из системы
     *
     * @param string $email
     * @param string $password
     * @return void|array
     */
    #[OA\Post(path: '/api/v1/user/logout', tags: ['user'])]
    #[OA\Response(response: 200, description: 'Успешный выход')]
    #[OA\Response(response: 400, description: 'Неверные данные')]
    public function logoutAction()
    {
        /** @var \CUser $USER */
        global $USER;
        $USER->Logout();
    }

    /**
     * Отправляет ссылку для восстановления пароля по почте
     *
     * @param string $email
     * @return void|array
     */
    #[OA\Post(path: '/api/v1/user/passwordSend', tags: ['user'])]
    #[OA\QueryParameter(name: 'email')]
    #[OA\Response(response: 200, description: 'Ссылка для смены пароля выслана на указанную почту')]
    #[OA\Response(response: 404, description: 'Пользователь не найден')]
    #[OA\Response(response: 400, description: 'Ошибка восстановления пароля')]
    public function passwordSendAction($email)
    {
        $arUser = \Bitrix\Main\UserTable::getList([
            'filter' => ['EMAIL' => $email],
            'limit' => 1,
            'select' => ['LOGIN']
        ])->fetch();

        if (empty($arUser)) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Пользователь не найден', 'USER_NOT_FOUND'));
            return null;
        }

        $login = !empty($arUser['LOGIN']) ?? $email;
        /** @var \CUser $USER */
        global $USER;
        $result = $USER->SendPassword($login, $email);

        if ($result['TYPE'] == 'ERROR') {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error($result['MESSAGE'], 'SEND_ERROR'));
            return null;
        } else {
            Context::getCurrent()->getResponse()->setStatus(200);
            return ['text' => $result['MESSAGE']];
        }
    }

    /**
     * Меняет пароль по контрольной строке
     *
     * @param string $email
     * @param string $email
     * @return void|array
     */
    #[OA\Post(path: '/api/v1/user/passwordChange', tags: ['user'])]
    #[OA\QueryParameter(name: 'email')]
    #[OA\QueryParameter(name: 'checkword')]
    #[OA\QueryParameter(name: 'password')]
    #[OA\QueryParameter(name: 'passwordRepeat')]
    #[OA\Response(response: 200, description: 'Пароль успешно изменен')]
    #[OA\Response(response: 404, description: 'Пользователь не найден')]
    #[OA\Response(response: 400, description: 'Ошибка изменения пароля')]
    public function passwordChangeAction($email, $checkword, $password, $passwordRepeat)
    {
        if (strlen($password) < 6) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Пароль должен быть не короче 6 символов', 'PASSWORD_SHORT'));
            return null;
        }

        if ($password !== $passwordRepeat) {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error('Повтор пароля не совпадает с паролем', 'PASSWORD_DIFFERENT'));
            return null;
        }

        $arUser = \Bitrix\Main\UserTable::getList([
            'filter' => ['EMAIL' => $email],
            'limit' => 1,
            'select' => ['LOGIN']
        ])->fetch();

        if (empty($arUser)) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Пользователь не найден', 'USER_NOT_FOUND'));
            return null;
        }

        $login = !empty($arUser['LOGIN']) ?? $email;
        $user = new \CUser();
        $result = $user->ChangePassword($login, $checkword, $password, $passwordRepeat);

        if ($result['TYPE'] == 'ERROR') {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new Error($result['MESSAGE'], 'SEND_ERROR'));
            return null;
        } else {
            Context::getCurrent()->getResponse()->setStatus(200);
            return ['text' => $result['MESSAGE']];
        }
    }
}
