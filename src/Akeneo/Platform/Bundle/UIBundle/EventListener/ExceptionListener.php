<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

/**
 * Redirects to hash url after login
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExceptionListener extends BaseExceptionListener
{
    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest() || !($exception instanceof AuthenticationException)) {
            return parent::onKernelException($event);
        }

        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        // Send the modified response object to the event
        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function setTargetPath(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($request->hasSession() && $request->isMethodSafe()) {
            if (null !== $qs = $request->getQueryString()) {
                $qs = '?' . $qs;
            }
            $targetPath = $request->getBaseUrl() . $request->getPathInfo() . $qs;

            $request->getSession()->set('__target_path', $targetPath);
        }
    }
}
