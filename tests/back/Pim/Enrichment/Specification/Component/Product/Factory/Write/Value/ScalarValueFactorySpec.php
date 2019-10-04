<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\ScalarValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Prophecy\Argument;

class ScalarValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ScalarValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ScalarValueFactory::class);
    }

    function it_throws_an_exception_when_creating_an_empty_text_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_text');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('text_attribute');
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_text_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_text');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('text_attribute');
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_a_text_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_text');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('text_attribute');
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('text_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->getData()->shouldReturn('foobar');
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_text');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('text_attribute');
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_textarea_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_textarea');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(true);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('textarea_attribute');
        $attribute->getType()->willReturn('pim_catalog_textarea');
        $attribute->getBackendType()->willReturn('textarea');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_textarea_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_textarea');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(true);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('textarea_attribute');
        $attribute->getType()->willReturn('pim_catalog_textarea');
        $attribute->getBackendType()->willReturn('textarea');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_an_textarea_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_textarea');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(true);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('textarea_attribute');
        $attribute->getType()->willReturn('pim_catalog_textarea');
        $attribute->getBackendType()->willReturn('textarea');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('textarea_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->getData()->shouldReturn('foobar');
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopablearea_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_textarea');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(true);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('textarea_attribute');
        $attribute->getType()->willReturn('pim_catalog_textarea');
        $attribute->getBackendType()->willReturn('textarea');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_integer_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_number');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('integer_attribute');
        $attribute->getType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('integer');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_integer_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_number');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('integer_attribute');
        $attribute->getType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('integer');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_an_numeric_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_number');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('integer_attribute');
        $attribute->getType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('integer');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            42
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('integer_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->getData()->shouldReturn(42);
    }

    function it_creates_a_localizable_and_scopable_numeric_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_number');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('integer_attribute');
        $attribute->getType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('integer');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            42
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('integer_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->getData()->shouldReturn(42);
    }

    function it_throws_an_exception_when_creating_an_empty_boolean_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_boolean');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(true);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('boolean_attribute');
        $attribute->getType()->willReturn('pim_catalog_boolean');
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_boolean_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_boolean');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(true);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('boolean_attribute');
        $attribute->getType()->willReturn('pim_catalog_boolean');
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_a_boolean_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_boolean');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(true);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('boolean_attribute');
        $attribute->getType()->willReturn('pim_catalog_boolean');
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            true
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('boolean_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->getData()->shouldReturn(true);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_boolean_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_boolean');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(true);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('boolean_attribute');
        $attribute->getType()->willReturn('pim_catalog_boolean');
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_an_empty_identifier_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_identifier');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('identifier_attribute');
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_an_identifier_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_identifier');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(false);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('identifier_attribute');
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('identifier_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->getData()->shouldReturn('foobar');
    }

    function it_throws_an_exception_when_provided_data_is_not_a_scalar(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ScalarValue::class, 'pim_catalog_text');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_text')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_textarea')->shouldReturn(false);
        $this->supports('pim_catalog_boolean')->shouldReturn(false);
        $this->supports('pim_catalog_identifier')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('text_attribute');
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::scalarExpected(
            'text_attribute',
            ScalarValueFactory::class,
            ['foo' => 'bar']
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['foo' => 'bar']]);
    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'    => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'       => function ($subject) {
                return null === $subject->getData();
            },
        ];
    }
}
