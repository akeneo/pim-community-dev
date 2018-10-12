<?php

namespace spec\Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Negotiation\FormatNegotiator;
use Negotiation\AcceptHeader;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckHeadersRequestSubscriberSpec extends ObjectBehavior
{
    function let(
        FormatNegotiator $formatNegotiator,
        ContentTypeNegotiator $contentTypeNegotiator,
        GetResponseEvent $event
    ) {
        $this->beConstructedWith($formatNegotiator, $contentTypeNegotiator);
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()
            ->shouldReturn([KernelEvents::REQUEST => 'onKernelRequest']);
    }

    public function it_successfully_validates_default_accept_header(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest('*/*')->willReturn($best);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('GET');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('accept', null)->willReturn('*/*');

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_successfully_validates_json_accept_header(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest('application/json')->willReturn($best);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('GET');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('accept', null)->willReturn('application/json');

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_throws_an_exception_when_accept_header_is_xml(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest('application/xml')->willReturn($best);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('GET');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('accept', null)->willReturn('application/xml');

        $this->shouldThrow(new NotAcceptableHttpException('"application/xml" in "Accept" header is not valid. Only "application/json" is allowed.'))
            ->during('onKernelRequest', [$event]);
    }

    public function it_successfully_validates_json_content_type_header(
        $event,
        $contentTypeNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes
    ) {
        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type')->willReturn('application/json');

        $contentTypeNegotiator->getContentTypes($request)->willReturn(['application/json', 'application/xml']);

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_successfully_validates_form_data_content_type_header(
        $event,
        $contentTypeNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes
    ) {
        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type')->willReturn('multipart/form-data; boundary=foo');

        $contentTypeNegotiator->getContentTypes($request)->willReturn(['multipart/form-data']);

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_throws_an_exception_when_content_type_header_is_xml_instead_of_json(
        $event,
        $contentTypeNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes
    ) {
        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn('application/xml');

        $contentTypeNegotiator->getContentTypes($request)->willReturn(['application/json']);

        $this->shouldThrow(new UnsupportedMediaTypeHttpException('"application/xml" in "Content-Type" header is not valid. Only "application/json" is allowed.'))
            ->during('onKernelRequest', [$event]);
    }

    public function it_throws_an_exception_when_content_type_header_is_xml_instead_of_json_or_form_data(
        $event,
        $contentTypeNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes
    ) {
        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn('application/xml');

        $contentTypeNegotiator->getContentTypes($request)->willReturn(['application/json', 'multipart/form-data']);

        $this->shouldThrow(new UnsupportedMediaTypeHttpException('"application/xml" in "Content-Type" header is not valid. Only "application/json" or "multipart/form-data" are allowed.'))
            ->during('onKernelRequest', [$event]);
    }


    public function it_throws_an_exception_when_content_type_is_missing(
        $event,
        $contentTypeNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes
    ) {
        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn();

        $contentTypeNegotiator->getContentTypes($request)->willReturn(['application/json', 'application/xml']);

        $this->shouldThrow(new UnsupportedMediaTypeHttpException('The "Content-Type" header is missing. "application/json" or "application/xml" has to be specified as value.'))
            ->during('onKernelRequest', [$event]);
    }

    public function it_stops_if_uri_is_not_in_api_with_get_request(
        $event,
        $formatNegotiator,
        ParameterBag $headers,
        Request $request,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest('')->willThrow('FOS\RestBundle\Util\StopFormatListenerException');
        $request->headers = $headers;
        $headers->get('accept')->willReturn('');
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $request->getMethod()->willReturn('GET');

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_stops_if_uri_is_not_in_api_with_patch_request(
        $event,
        $contentTypeNegotiator,
        ParameterBag $headers,
        Request $request,
        ParameterBag $attributes
    ) {
        $contentTypeNegotiator->getContentTypes($request)->willThrow('FOS\RestBundle\Util\StopFormatListenerException');
        $request->headers = $headers;
        $headers->get('content-type')->willReturn('');
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $request->getMethod()->willReturn('PATCH');

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_returns_null_if_request_is_not_a_master_request(
        $event,
        $formatNegotiator,
        ParameterBag $headers,
        Request $request,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest('*/*')->willThrow('FOS\RestBundle\Util\StopFormatListenerException');
        $headers->get('accept')->willReturn('*/*');
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::SUB_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $best->getValue()->shouldNotBeCalled();
        $request->getMethod()->shouldNotBeCalled();

        $this->onKernelRequest($event)->shouldReturn(null);
    }
}

interface CustomAcceptHeader extends AcceptHeader
{
    public function getValue();
}
