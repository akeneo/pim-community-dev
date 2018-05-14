<?php

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Akeneo\Tool\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator;
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

    /** @var ContentTypeNegotiator */
    protected $contentTypeNegotiator;

    /**
     * @param FormatNegotiator      $formatNegotiator
     * @param ContentTypeNegotiator $contentTypeNegotiator
     */
    public function __construct(
        FormatNegotiator $formatNegotiator,
        ContentTypeNegotiator $contentTypeNegotiator
    ) {
        $this->formatNegotiator = $formatNegotiator;
        $this->contentTypeNegotiator = $contentTypeNegotiator;
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
     * Check the content-type and accept headers in the request.
     *
     * @param GetResponseEvent $event The event
     *
     * @throws NotAcceptableHttpException
     * @throws UnsupportedMediaTypeHttpException
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
            if ('GET' === $request->getMethod()) {
                $bestAcceptType = $this->formatNegotiator->getBest($request->headers->get('accept'));

                if (null === $bestAcceptType) {
                    return;
                }

                $accept = $request->headers->get('accept', null);
                if (null !== $accept && $accept !== $bestAcceptType->getValue() && !preg_match('|\*\/\*|', $accept)) {
                    throw new NotAcceptableHttpException(
                        sprintf('"%s" in "Accept" header is not valid. Only "%s" is allowed.', $accept, $bestAcceptType->getValue())
                    );
                }

                return;
            }

            if (in_array($request->getMethod(), ['PUT', 'PATCH', 'POST'])) {
                $contentType = trim(strtok($request->headers->get('content-type'), ';'));
                $allowedContentTypes = $this->contentTypeNegotiator->getContentTypes($request);

                if ('' === $contentType) {
                    throw new UnsupportedMediaTypeHttpException(
                        sprintf(
                            'The "Content-Type" header is missing. "%s" has to be specified as value.',
                            implode('" or "', $allowedContentTypes)
                        )
                    );
                }

                if (!empty($allowedContentTypes) && !in_array($contentType, $allowedContentTypes)) {
                    $plural = count($allowedContentTypes) > 1 ? 'are' : 'is';
                    throw new UnsupportedMediaTypeHttpException(
                        sprintf(
                            '"%s" in "Content-Type" header is not valid. Only "%s" %s allowed.',
                            $contentType,
                            implode('" or "', $allowedContentTypes),
                            $plural
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
