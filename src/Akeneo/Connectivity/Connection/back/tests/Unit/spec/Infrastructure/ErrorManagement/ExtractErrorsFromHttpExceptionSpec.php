<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExtractErrorsFromHttpExceptionSpec extends ObjectBehavior
{
    public function let(Serializer $serializer): void
    {
        $this->beConstructedWith($serializer);
    }

    public function it_extracts_an_error_from_an_unprocessable_entity_http_exception($serializer): void
    {
        $exception = new UnprocessableEntityHttpException();

        $serializer->serialize($exception, 'json', new Context())->willReturn('{"message":"My error!"}');

        $this->extractAll($exception)->shouldReturn(['{"message":"My error!"}']);
    }

    public function it_extracts_an_error_from_a_not_found_http_exception($serializer): void
    {
        $exception = new NotFoundHttpException();

        $serializer->serialize($exception, 'json', new Context())->willReturn('{"message":"My error!"}');

        $this->extractAll($exception)->shouldReturn(['{"message":"My error!"}']);
    }

    public function it_extracts_multiple_errors_from_a_violation_http_exception($serializer, ViolationHttpException $violationHttpException): void
    {
        $serializer->serialize($violationHttpException, 'json', new Context())
            ->willReturn('{"errors":[{"message":"First error!"},{"message":"Second error!"}]}');

        $this->extractAll($violationHttpException)->shouldReturn([
            '{"message":"First error!"}',
            '{"message":"Second error!"}'
        ]);
    }

    public function it_skips_other_http_exception(): void
    {
        $exception = new HttpException(400);

        $this->extractAll($exception)->shouldReturn([]);
    }
}
