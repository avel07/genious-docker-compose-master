<?php

namespace Cube\Middleware;

use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter\Base;

/**
 * Class Cors
 * Set headers for CORS.
 */
final class Cors extends Base
{
    public function onBeforeAction(Main\Event $event): void
    {
        $this->setCorsHeaders();
    }

    public function onAfterAction(Main\Event $event): void
    {
        $this->setCorsHeaders();
    }

    private function setCorsHeaders(): void
    {
        $context = Main\Context::getCurrent();
        if (!$context) {
            return;
        }

        $response = $context->getResponse();
        $origin = $context->getRequest()->getHeader('Origin'); // TODO: сделать cors для определенных доменов
        if ($origin && $response instanceof Main\HttpResponse) {
            $currentHttpHeaders = $response->getHeaders();
            $currentHttpHeaders->add('Access-Control-Allow-Origin', $origin);
            $currentHttpHeaders->add('Access-Control-Allow-Credentials', 'true');
        }
    }
}
