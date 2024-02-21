<?php

namespace spec\Akeneo\Tool\Component\Api\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationHttpExceptionSpec extends ObjectBehavior
{
    function it_creates_an_object_updater_http_exception(ConstraintViolationListInterface $constraintViolation)
    {
        $previous = new \Exception();

        $this->beConstructedWith($constraintViolation, 'Property "xx" does not exist', $previous, 0);

        $this->shouldBeAnInstanceOf(ViolationHttpException::class);
        $this->getViolations()->shouldReturn($constraintViolation);
        $this->getMessage()->shouldReturn('Property "xx" does not exist');
        $this->getCode()->shouldReturn(0);
        $this->getPrevious()->shouldReturn($previous);
        $this->getStatusCode()->shouldReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
