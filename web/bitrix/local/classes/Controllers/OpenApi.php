<?php

namespace Cube\Controllers;

use Bitrix\Main\Application;
use OpenApi\Attributes as OA;
use Cube\Controllers\BaseController;

// Определяем локальный домен
define('DOMAIN_NAME_BACKEND', getenv('DOMAIN_NAME_BACKEND') ? '//' . getenv('DOMAIN_NAME_BACKEND') : '//' . SITE_SERVER_NAME);

#[OA\Info(title: SITE_SERVER_NAME ? SITE_SERVER_NAME : 'Bitrix API', version: "1.0")]
#[OA\Server(url: DOMAIN_NAME_BACKEND)]
#[OA\Server(url: SITE_SERVER_NAME ? 'https://' . SITE_SERVER_NAME : null)]
class OpenApi extends BaseController
{
    #[OA\Get(path: '/api/v1/openapi')]
    #[OA\Response(response: 200, description: 'Сегенерированный YAML файл Openapi 3.0')]
    public function generateAction()
    {
        $openapi = \OpenApi\Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/local']);
        $application = Application::getInstance();
        $response = $application->getContext()->getResponse();
        $response->addHeader('Content-Type', 'application/x-yaml');
        $response->setContent($openapi->toYaml());
        $application->end(200, $response);
    }
}
