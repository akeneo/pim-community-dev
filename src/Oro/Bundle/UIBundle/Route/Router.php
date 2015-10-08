<?php

namespace Oro\Bundle\UIBundle\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router as SymfonyRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    const ACTION_PARAMETER = 'input_action';

    const ACTION_SAVE_AND_STAY = 'save_and_stay';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var SymfonyRouter
     */
    protected $router;

    public function __construct(Request $request, SymfonyRouter $router)
    {
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * "Save and Stay" and "Save and Close" buttons handler
     *
     * @param array $saveButtonRoute array with router data for save and stay button
     * @param array $saveAndCloseRoute array with router data for save and close button
     * @param int $status redirect status
     *
     * @return RedirectResponse
     * @throws \LogicException
     */
    public function actionRedirect(array $saveButtonRoute, array $saveAndCloseRoute, $status = 302)
    {
        if ($this->request->get(self::ACTION_PARAMETER) == self::ACTION_SAVE_AND_STAY) {
            $routeData = $saveButtonRoute;
        } else {
            $routeData = $saveAndCloseRoute;
        }

        if (!isset($routeData['route'])) {
            throw new \LogicException('Parameter "route" is not defined.');
        } else {
            $routeName = $routeData['route'];
        }

        $params = isset($routeData['parameters']) ? $routeData['parameters'] : array();

        return new RedirectResponse(
            $this->router->generate(
                $routeName,
                $params,
                UrlGeneratorInterface::ABSOLUTE_PATH
            ),
            $status
        );
    }
}
