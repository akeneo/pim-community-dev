<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeValueTypeException;
use PhpSpec\ObjectBehavior;

class InvalidAttributeValueTypeExceptionSpec extends ObjectBehavior
{
    function it_creates_a_not_string_exception()
    {
        $exception = InvalidAttributeValueTypeException::stringExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            new TemplatedErrorMessage(
                'The {attribute} attribute requires a string, a {invalid} was detected.',
                [
                    'attribute' => 'attribute',
                    'invalid' => gettype([]),
                ]
            ),
            InvalidAttributeValueTypeException::STRING_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
        $templatedMessage = $this->getTemplatedErrorMessage();
        $templatedMessage->shouldBeAnInstanceOf(TemplatedErrorMessage::class);
        $templatedMessage->__toString()->shouldReturn('The attribute attribute requires a string, a array was detected.');
    }
}
