<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\DateValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;

class DateValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(DateValue::class, 'pim_catalog_date');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateValueFactory::class);
    }

    function it_supports_date_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_date')->shouldReturn(true);
    }

    function it_creates_an_empty_date_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(DateValue::class);
        $productValue->shouldHaveAttribute('date_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_date_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(DateValue::class);
        $productValue->shouldHaveAttribute('date_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_date_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            '2000-01-01'
        );

        $productValue->shouldReturnAnInstanceOf(DateValue::class);
        $productValue->shouldHaveAttribute('date_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveDate('2000-01-01');
    }

    function it_creates_a_localizable_and_scopable_date_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            '2000-01-01'
        );

        $productValue->shouldReturnAnInstanceOf(DateValue::class);
        $productValue->shouldHaveAttribute('date_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveDate('2000-01-01');
    }

    function it_throws_an_exception_when_provided_data_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::stringExpected(
            'date_attribute',
            DateValueFactory::class,
            []
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', []]);
    }

    function it_throws_an_exception_when_provided_data_is_not_a_date(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyException::dateExpected(
            'date_attribute',
            'yyyy-mm-dd',
            DateValueFactory::class,
            'foobar is no date'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar is no date']);
    }

    function it_throws_an_exception_when_provided_date_format_is_invalid(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('date_attribute');
        $attribute->getType()->willReturn('pim_catalog_date');
        $attribute->getBackendType()->willReturn('date');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyException::dateExpected(
            'date_attribute',
            'yyyy-mm-dd',
            DateValueFactory::class,
            '03-04-2013'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', '03-04-2013']);
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
            'haveDate'      => function ($subject, $expectedDate) {
                return $expectedDate === $subject->getData()->format('Y-m-d');
            },
        ];
    }
}
