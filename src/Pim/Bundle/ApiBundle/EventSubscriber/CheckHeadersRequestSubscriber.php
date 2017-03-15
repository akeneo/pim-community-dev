<?php

namespace Pim\Bundle\ApiBundle\EventSubscriber;

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Negotiation\FormatNegotiator;
use FOS\RestBundle\Util\StopFormatListenerException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Check headers for the API:
 *    - for GET, only application/json in Accept header is allowed
 *    - for PUT, POST & PATCH, only application/json in Content-Type header is allowed
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckHeadersRequestSubscriber implements EventSubscriberInterface
{
    /** @var FormatNegotiator */
    protected $formatNegotiator;

    /**
     * @param FormatNegotiator $formatNegotiator
     */
    public function __construct(FormatNegotiator $formatNegotiator)
    {
        $this->formatNegotiator = $formatNegotiator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     * Check the headers in Request
     *
     * @param GetResponseEvent $event The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(FOSRestBundle::ZONE_ATTRIBUTE) ||
            $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST
        ) {
            return;
        }

        try {
            $best = $this->formatNegotiator->getBest($request->headers->get('accept'));

            if (null === $best) {
                return;
            }

            if ('GET' === $request->getMethod()) {
                $accept = $request->headers->get('accept', null);
                if (null !== $accept && $accept !== $best->getValue() && !preg_match('|\*\/\*|', $accept)) {
                    throw new NotAcceptableHttpException(
                        sprintf('"%s" in "Accept" header is not valid. Only "%s" is allowed.', $accept, $best->getValue())
                    );
                }

                return;
            }

            if (in_array($request->getMethod(), ['PUT', 'PATCH', 'POST'])) {
                $contentType = $request->headers->get('content-type');
                if (null === $contentType) {
                    throw new UnsupportedMediaTypeHttpException(
                        sprintf(
                            'The "Content-Type" header is missing. "%s" has to be specified as value.',
                            $best->getValue()
                        )
                    );
                }

                if (false === strpos($contentType, $best->getValue())) {
                    throw new UnsupportedMediaTypeHttpException(
                        sprintf(
                            '"%s" in "Content-Type" header is not valid. Only "%s" is allowed.',
                            $contentType,
                            $best->getValue()
                        )
                    );
                }
            }
        } catch (StopFormatListenerException $e) {
            // do nothing.
            // StopFormatListenerException is thrown when the URI is outside the API
        }
    }
}
