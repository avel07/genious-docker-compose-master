<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

// Если это не админ, отдаем 404 ошибку
if (!\Bitrix\Main\Engine\CurrentUser::get()->isAdmin()) {
    require_once(\Bitrix\Main\Application::getDocumentRoot() . "/404.php");
} else {
    require_once(\Bitrix\Main\Application::getDocumentRoot() . '/swagger/index.php');
}
