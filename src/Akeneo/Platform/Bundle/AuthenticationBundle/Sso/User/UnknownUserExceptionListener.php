<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

/**
 * Displays an error page when an unknown user exception is thrown.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class UnknownUserExceptionListener
{
    private Environment $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->getThrowable() instanceof UnknownUserException) {
            return;
        }

        $response = new Response(
            $this->templating->render('AkeneoAuthenticationBundle::error.html.twig')
        );
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}
