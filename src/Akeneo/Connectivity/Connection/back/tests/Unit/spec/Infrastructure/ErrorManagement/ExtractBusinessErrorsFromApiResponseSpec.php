<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class ExtractBusinessErrorsFromApiResponseSpec extends ObjectBehavior
{
    public function it_extracts_a_business_error_from_an_api_http_unprocessable_entity_response(
        Response $response
    ): void {
        $response->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $content = <<<JSON
{
    "code": 422,
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#post_products"
        }
    },
    "message": "Property \"description\" does not exist. Check the expected format on the API documentation."
}
JSON;

        $response->getContent()->willReturn($content);
        $businessErrors = $this->extractAll($response, 'erp');
        $businessErrors->shouldBeArray();
        $businessErrors->shouldHaveCount(1);
        $businessErrors[0]->shouldBeAnInstanceOf(BusinessError::class);

        $businessErrors[0]->connectionCode()->__toString()->shouldBe('erp');
        $businessErrors[0]->content()->shouldBe($content);
    }

    public function it_extracts_a_business_error_from_an_api_http_unprocessable_entity_response_with_errors(
        Response $response
    ): void {
        $response->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $identifierError = <<<JSON
{"property":"identifier","message":"The same identifier is already set on another product"}
JSON;
        $metricError = <<<JSON
{"property":"values","message":"Please specify a valid metric unit","attribute":"length","locale":null,"scope":null}
JSON;
        $body = <<<JSON
{
    "code": 422,
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#post_products"
        }
    },
    "errors": [
        %s,
        %s
    ],
    "message": "Validation failed."
}
JSON;
        $content = sprintf($body, $identifierError, $metricError);

        $response->getContent()->willReturn($content);
        $businessErrors = $this->extractAll($response, 'erp');
        $businessErrors->shouldBeArray();
        $businessErrors->shouldHaveCount(2);

        $businessErrors[0]->shouldBeAnInstanceOf(BusinessError::class);
        $businessErrors[0]->connectionCode()->__toString()->shouldBe('erp');
        $businessErrors[0]->content()->shouldBe($identifierError);

        $businessErrors[1]->shouldBeAnInstanceOf(BusinessError::class);
        $businessErrors[1]->connectionCode()->__toString()->shouldBe('erp');
        $businessErrors[1]->content()->shouldBe($metricError);
    }
}
