<?php

namespace spec\Pim\Bundle\ApiBundle\EventSubscriber;

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Negotiation\FormatNegotiator;
use Negotiation\AcceptHeader;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckHeadersRequestSubscriberSpec extends ObjectBehavior
{
    public function let(FormatNegotiator $formatNegotiator, GetResponseEvent $event)
    {
        $this->beConstructedWith($formatNegotiator);
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()
            ->shouldReturn([KernelEvents::REQUEST => 'onKernelRequest']);
    }

    public function it_successfully_valid_default_accept_header(
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

    public function it_successfully_valid_json_accept_header(
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

    public function it_successfully_valid_json_content_type_header(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest(null)->willReturn($best);
        $headers->get('accept')->willReturn(null);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn('application/json');

        $this->onKernelRequest($event)->shouldReturn(null);
    }

    public function it_throws_an_exception_when_content_type_header_is_xml(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest(null)->willReturn($best);
        $headers->get('accept')->willReturn(null);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn('application/xml');

        $this->shouldThrow(new UnsupportedMediaTypeHttpException('"application/xml" in "Content-Type" header is not valid. Only "application/json" is allowed.'))
            ->during('onKernelRequest', [$event]);
    }


    public function it_throws_an_exception_when_content_type_is_missing(
        $event,
        $formatNegotiator,
        Request $request,
        ParameterBag $headers,
        ParameterBag $attributes,
        CustomAcceptHeader $best
    ) {
        $formatNegotiator->getBest(null)->willReturn($best);
        $headers->get('accept')->willReturn(null);
        $best->getValue()->willReturn('application/json');

        $event->getRequest()->willReturn($request);
        $request->getMethod()->willReturn('POST');
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $request->headers = $headers;
        $headers->get('content-type', null)->willReturn();

        $this->shouldThrow(new UnsupportedMediaTypeHttpException('The "Content-Type" header is missing. "application/json" has to be specified as value.'))
            ->during('onKernelRequest', [$event]);
    }

    public function it_stops_if_uri_is_not_in_api(
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

        $request->attributes = $attributes;
        $attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)->willReturn(true);

        $best->getValue()->shouldNotBeCalled();
        $request->getMethod()->shouldNotBeCalled();

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
