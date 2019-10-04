<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\OptionValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;

class OptionValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(OptionValue::class, AttributeTypes::OPTION_SIMPLE_SELECT);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionValueFactory::class);
    }

    function it_supports_simpleselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports(AttributeTypes::OPTION_SIMPLE_SELECT)->shouldReturn(true);
    }

    function it_throws_an_exception_when_creating_an_empty_simple_select_product_value(AttributeInterface $attribute) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_simple_select_product_value(AttributeInterface $attribute) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_a_simple_select_product_value(AttributeInterface $attribute) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(OptionValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptionCode('foobar');
    }

    function it_creates_a_localizable_and_scopable_simple_select_product_value(AttributeInterface $attribute) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(OptionValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOptionCode('foobar');
    }

    function it_throws_an_exception_if_invalid_data_is_provided(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $booleanException = InvalidPropertyTypeException::stringExpected(
            'simple_select_attribute',
            OptionValueFactory::class,
            true
        );

        $arrayException = InvalidPropertyTypeException::stringExpected(
            'simple_select_attribute',
            OptionValueFactory::class,
            []
        );

        $this
            ->shouldThrow($booleanException)
            ->during('create', [$attribute, 'ecommerce', 'en_US', true]);

        $this
            ->shouldThrow($arrayException)
            ->during('create', [$attribute, 'ecommerce', 'en_US', []]);
    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute'  => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable'  => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'     => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'     => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'    => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'        => function ($subject) {
                return null === $subject->getData();
            },
            'haveTheOptionCode'  => function ($subject, $option) {
                return $option === $subject->getData();
            },
        ];
    }
}
