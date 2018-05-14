<?php

namespace spec\Akeneo\Tool\Component\Api\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Symfony\Component\HttpFoundation\Response;

class DocumentedHttpExceptionSpec extends ObjectBehavior
{
    function it_creates_an_object_updater_http_exception()
    {
        $previous = new \Exception();

        $this->beConstructedWith('http://example.com', 'Property "xx" does not exist', $previous, 0);

        $this->shouldBeAnInstanceOf(DocumentedHttpException::class);
        $this->getHref()->shouldReturn('http://example.com');
        $this->getMessage()->shouldReturn('Property "xx" does not exist');
        $this->getCode()->shouldReturn(0);
        $this->getPrevious()->shouldReturn($previous);
        $this->getStatusCode()->shouldReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
