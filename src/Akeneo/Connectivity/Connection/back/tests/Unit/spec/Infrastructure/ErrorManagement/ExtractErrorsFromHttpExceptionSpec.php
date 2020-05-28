<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ExtractErrorsFromHttpExceptionSpec extends ObjectBehavior
{
    public function let(Serializer $serializer, Serializer $fallbackSerializer): void
    {
        $this->beConstructedWith($serializer, $fallbackSerializer);
    }

    public function it_extracts_an_error_from_an_unprocessable_entity_http_exception($serializer): void
    {
        $exception = new UnprocessableEntityHttpException();
        $connectionCode = new ConnectionCode('erp');

        $serializer->serialize($exception, 'json', new Context())->willReturn('{"message":"My error!"}');

        $result = $this->extractAll($exception, $connectionCode);
        $result->shouldBeArray();
        $result->shouldHaveCount(1);
        $result[0]->shouldBeAnInstanceOf(TechnicalError::class);
    }

    public function it_extracts_an_error_from_an_unknown_attribute_http_exception($serializer): void
    {
        $exception = new UnprocessableEntityHttpException(
            '{"message":"My error!"}',
            new UnknownAttributeException('description')
        );
        $connectionCode = new ConnectionCode('erp');

        $serializer->serialize($exception, 'json', new Context())->willReturn('{"message":"My error!"}');

        $result = $this->extractAll($exception, $connectionCode);
        $result->shouldBeArray();
        $result->shouldHaveCount(1);
        $result[0]->shouldBeAnInstanceOf(BusinessError::class);
    }

    public function it_extracts_an_error_from_a_not_found_http_exception($serializer): void
    {
        $exception = new NotFoundHttpException();
        $connectionCode = new ConnectionCode('erp');

        $serializer->serialize($exception, 'json', new Context())->willReturn('{"message":"My error!"}');

        $result = $this->extractAll($exception, $connectionCode);
        $result->shouldBeArray();
        $result->shouldHaveCount(1);
        $result[0]->shouldBeAnInstanceOf(TechnicalError::class);
    }

    public function it_extracts_multiple_errors_from_a_violation_http_exception($serializer, ViolationHttpException $violationHttpException): void
    {
        $connectionCode = new ConnectionCode('erp');
        $serializer->serialize($violationHttpException, 'json', new Context())
            ->willReturn('{"errors":[{"message":"First error!"},{"message":"Second error!"}]}');

        $result = $this->extractAll($violationHttpException, $connectionCode);
        $result->shouldBeArray();
        $result->shouldHaveCount(2);
        $result[0]->shouldBeAnInstanceOf(BusinessError::class);
        $result[1]->shouldBeAnInstanceOf(BusinessError::class);
    }

    public function it_skips_other_http_exception(): void
    {
        $connectionCode = new ConnectionCode('erp');
        $exception = new HttpException(400);

        $this->extractAll($exception, $connectionCode)->shouldReturn([]);
    }
}
