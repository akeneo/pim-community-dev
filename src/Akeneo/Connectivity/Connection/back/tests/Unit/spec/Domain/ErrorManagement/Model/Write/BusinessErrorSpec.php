<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use PhpSpec\ObjectBehavior;

class BusinessErrorSpec extends ObjectBehavior
{
    public function it_is_a_business_error(): void
    {
        $this->beConstructedWith($this->getWellStructuredContent());

        $this->shouldHaveType(BusinessError::class);
        $this->shouldImplement(ApiErrorInterface::class);
    }

    public function it_provides_a_content(): void
    {
        $this->beConstructedWith($this->getWellStructuredContent());

        $this->content()->shouldReturn($this->getWellStructuredContent());
    }

    public function it_must_have_a_json_content(): void
    {
        $this->beConstructedWith('');

        $this
            ->shouldThrow(
                \InvalidArgumentException::class
            )
            ->duringInstantiation();
    }

    public function it_must_have_a_content(): void
    {
        $this->beConstructedWith('{}');

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'The API error must have a content, but you provided en empty json.'
                )
            )
            ->duringInstantiation();
    }

    public function it_normalizes(): void
    {
        $dateTime = new \DateTimeImmutable('2020-01-01T00:00:00', new \DateTimeZone('UTC'));
        $content = $this->getWellStructuredContent();
        $this->beConstructedWith($content, $dateTime);

        $expected = [
            'id' => $this->id(),
            'content' => \json_decode($content, true, 512, JSON_THROW_ON_ERROR),
            'error_datetime' => '2020-01-01T00:00:00+00:00',
        ];

        $this->normalize()->shouldReturn($expected);
    }

    public function it_provides_an_error_type(): void
    {
        $this->beConstructedWith($this->getWellStructuredContent());

        $type = $this->type();
        $type->shouldBeAnInstanceOf(ErrorType::class);
        $type->__toString()->shouldReturn(ErrorTypes::BUSINESS);
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
