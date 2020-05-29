<?php

namespace spec\Akeneo\Tool\Component\Connector\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class InvalidItemFromViolationsExceptionSpec extends ObjectBehavior
{
    function it_formats_a_violation_message_for_an_invalid_scalar(
        ConstraintViolationInterface $violation
    ) {
        $violation->getInvalidValue()->willReturn('my bad value');
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('invalid value');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: invalid value: my bad value' . PHP_EOL);
    }

    function it_formats_violation_message_for_an_invalid_stringifiable_object(
        ConstraintViolationInterface $violation
    ) {
        $class = new class {
            public function __toString()
            {
                return 'my object';
            }
        };
        $violation->getInvalidValue()->willReturn(new $class());
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('invalid value');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: invalid value: my object' . PHP_EOL);
    }

    function it_formats_violation_message_for_an_invalid_non_stringifiable_object(
        ConstraintViolationInterface $violation
    ) {
        $violation->getInvalidValue()->willReturn(new \stdClass());
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('Unexpected value');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: Unexpected value' . PHP_EOL);
    }

    function it_formats_violation_message_for_an_invalid_product_price(
        ConstraintViolationInterface $violation
    ) {
        $violation->getInvalidValue()->willReturn(new ProductPrice(3299.99, 'EUR'));
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('This value should be lower than 3000');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: This value should be lower than 3000: 3299.99 EUR' . PHP_EOL);
    }

    function it_formats_violation_message_for_an_invalid_array_of_strings(
        ConstraintViolationInterface $violation
    ) {
        $violation->getInvalidValue()->willReturn(['foo', 'bar', 'baz']);
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('unknown codes');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: unknown codes: [foo, bar, baz]' . PHP_EOL);
    }

    function it_formats_violation_message_for_an_invalid_array_of_objects(
        ConstraintViolationInterface $violation
    ) {
        $class = new class {
            private $data;
            public function __construct(string $data = 'foo')
            {
                $this->data = $data;
            }
            public function __toString()
            {
                return $this->data;
            }
        };

        $violation->getInvalidValue()->willReturn([new $class(), new $class('bar')]);
        $violation->getPropertyPath()->willReturn('foo.bar.baz');
        $violation->getMessage()->willReturn('unknown codes');

        $this->beConstructedWith(
            new ConstraintViolationList([$violation->getWrappedObject()]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn('foo.bar.baz: unknown codes: [foo, bar]' . PHP_EOL);
    }

    function it_formats_multiple_violations(
        ConstraintViolationInterface $firstViolation,
        ConstraintViolationInterface $secondViolation,
        ConstraintViolationInterface $thirdViolation
    ) {
        $firstViolation->getInvalidValue()->willReturn(['foo', 'bar']);
        $firstViolation->getPropertyPath()->willReturn('values.conditions-<all_channels>');
        $firstViolation->getMessage()->willReturn('Unknown conditions');

        $secondViolation->getInvalidValue()->willReturn(new ProductPrice(20.5646, 'USD'));
        $secondViolation->getPropertyPath()->willReturn('values.price-<all_locales>');
        $secondViolation->getMessage()->willReturn('Invalid price data');

        $thirdViolation->getInvalidValue()->willReturn([new \stdClass()]);
        $thirdViolation->getPropertyPath()->willReturn('values.collection-<all_channels>-<all_locales>');
        $thirdViolation->getMessage()->willReturn('This collection should contain at least 2 elements');

        $this->beConstructedWith(
            new ConstraintViolationList([
                $firstViolation->getWrappedObject(),
                $secondViolation->getWrappedObject(),
                $thirdViolation->getWrappedObject(),
            ]),
            new DataInvalidItem(['foo' => 'bar'])
        );

        $this->getMessage()->shouldReturn(<<<EOL
            values.conditions: Unknown conditions: [foo, bar]
            
            values.price: Invalid price data: 20.5646 USD
            
            values.collection: This collection should contain at least 2 elements
            
            EOL
        );
    }
}
