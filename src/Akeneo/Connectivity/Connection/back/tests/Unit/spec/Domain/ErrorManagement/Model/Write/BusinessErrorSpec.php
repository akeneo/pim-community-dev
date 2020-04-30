<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use PhpSpec\ObjectBehavior;

class BusinessErrorSpec extends ObjectBehavior
{
    public function it_is_a_business_error(): void
    {
        $connectionCode = new ConnectionCode('erp');
        $content = '{"message":"My error!"}';
        $dateTime = new \DateTimeImmutable('2020-01-01T00:00:00', new \DateTimeZone('UTC'));

        $this->beConstructedWith($connectionCode, $content, $dateTime);
        $this->shouldHaveType(BusinessError::class);
    }

    public function it_provides_a_connection_code_and_a_content()
    {
        $connectionCode = new ConnectionCode('erp');
        $this->beConstructedWith($connectionCode, $this->getWellStructuredContent());

        $this->connectionCode()->shouldReturnAnInstanceOf(ConnectionCode::class);
        $this->content()->shouldReturn($this->getWellStructuredContent());
    }

    public function it_must_have_a_json_content()
    {
        $connectionCode = new ConnectionCode('erp');
        $this->beConstructedWith($connectionCode, '');

        $this
            ->shouldThrow(
                \InvalidArgumentException::class
            )
            ->duringInstantiation();
    }

    public function it_must_have_a_content()
    {
        $connectionCode = new ConnectionCode('erp');
        $this->beConstructedWith($connectionCode, '{}');

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'The business error must have a content, but you provided en empty json.'
                )
            )
            ->duringInstantiation();
    }

    public function it_normalizes(): void
    {
        $connectionCode = new ConnectionCode('erp');
        $content = $this->getWellStructuredContent();
        $dateTime = new \DateTimeImmutable('2020-01-01T00:00:00', new \DateTimeZone('UTC'));

        $expected = [
            'connection_code' => 'erp',
            'content' => json_decode($content, true),
            'error_datetime' => '2020-01-01T00:00:00+00:00',
        ];

        $this->beConstructedWith($connectionCode, $content, $dateTime);
        $this->normalize()->shouldReturn($expected);
    }

    private function getWellStructuredContent(): string
    {
        return <<<JSON
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
    }
}
