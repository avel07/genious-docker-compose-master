<?php

namespace Cube\Controllers;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Response;

/**
 * Наследник класса контроллеров битрикса
 *
 * Исключения тоже обрабатываются в json
 * При включении debug в .settings.php показывается вывод вид ошибки
 *
 * @link https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=105&LESSON_ID=6436
 */
class BaseController extends \Bitrix\Main\Engine\Controller
{
    /**
     * Переопределяем префильтры
     *
     * Удаляем csrf и обязательную авторизацию
     *
     * @return void
     */
    protected function getDefaultPreFilters()
    {
        return [
            new ActionFilter\HttpMethod(
                [
                    ActionFilter\HttpMethod::METHOD_GET,
                    ActionFilter\HttpMethod::METHOD_POST,
                    ActionFilter\HttpMethod::METHOD_PUT,
                    ActionFilter\HttpMethod::METHOD_DELETE
                ]
            ),
            new ActionFilter\Csrf(false),
            new \Cube\Middleware\Cors()
        ];
    }

    /**
     * Finalizes response.
     * The method will be invoked when HttpApplication will be ready to send response to client.
     * It's a final place where Controller can interact with response.
     *
     * Если в контроллере произошли ошибки 400 ответ по умолчанию
     *
     * @param Response $response
     * @return void
     */
    public function finalizeResponse(Response $response)
    {
        if (!empty($this->getErrors()) && (!$response->getStatus() || $response->getStatus() == 200)) {
            $response->setStatus(400);
        }
    }

    /**
     * Временное решение из за ошибок в ядре (current user может быть пусто)
     * TODO: Удалить как выйдет фикс
     * 
	 * @param Controller|string $controller
	 * @param string     $actionName
	 * @param array|null      $parameters
	 *
	 * @return HttpResponse|mixed
	 * @throws SystemException
	 */
	public function forward($controller, string $actionName, array $parameters = null)
	{
		if (is_string($controller))
		{
			$controller = new $controller;
		}

		// override parameters
		$controller->request = $this->getRequest();
		$controller->setScope($this->getScope());;

		// run action
		$result = $controller->run(
			$actionName,
			$parameters === null ? $this->getSourceParametersList() : [$parameters]
		);

		$this->addErrors($controller->getErrors());

		return $result;
	}

    /**
     * Запуск экшена в контроллере
     *
     * Запускает стандартную обработку контроллера битрикс
     * Ответ отдает в json
     *
     * @param string $action
     * @return void
     */
    public function runAction(string $action)
    {
        /** @var \Bitrix\Main\HttpApplication $app */
        $app = \Bitrix\Main\Application::getInstance();
        $app->runController($this, $action);
    }
}
