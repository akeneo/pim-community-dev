<?php

namespace Pim\Bundle\EnrichBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Kernel listener to catch exceptions and display a nice error page
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class KernelExceptionListener
{
    /** @var EngineInterface */
    protected $templating;

    /**
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Manage kernel exception
     * @param GetResponseForExceptionEvent $event
     *
     * @return GetResponseForExceptionEvent
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(500);
        }

        $content = $this->templating->render(
            'PimEnrichBundle:Error:base.html.twig',
            [
                'exception'   => $exception,
                'status_code' => $response->getStatusCode()
            ]
        );

        $response->setContent($content);

        $event->setResponse($response);
    }
}
