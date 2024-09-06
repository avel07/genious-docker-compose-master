<?php

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$app = \Bitrix\Main\Application::getInstance();

$errorCollection = new ErrorCollection();
$errorCollection[] = new Error('API resource not found', "NOT_FOUND");

$response = new AjaxJson(
    null,
    AjaxJson::STATUS_ERROR,
    $errorCollection
);

$app->getContext()->setResponse($response);
$app->getContext()->getResponse()->setStatus(404);
$app->end(0, $response);
