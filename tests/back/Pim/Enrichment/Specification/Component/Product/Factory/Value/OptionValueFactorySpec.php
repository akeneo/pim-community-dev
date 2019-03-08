<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Prophecy\Argument;

class OptionValueFactorySpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attrOptionRepository)
    {
        $this->beConstructedWith($attrOptionRepository, OptionValue::class, 'pim_catalog_simpleselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionValueFactory::class);
    }

    function it_supports_simpleselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_simpleselect')->shouldReturn(true);
    }

    function it_creates_an_empty_simple_select_product_value(
        $attrOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(OptionValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_simple_select_product_value(
        $attrOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(OptionValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_simple_select_product_value(
        $attrOptionRepository,
        AttributeOptionInterface $option,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option->getCode()->willReturn('foobar');

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn($option);

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

    function it_creates_a_localizable_and_scopable_simple_select_product_value(
        $attrOptionRepository,
        AttributeOptionInterface $option,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option->getCode()->willReturn('foobar');

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn($option);

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

    function it_throws_an_exception_if_invalid_data_is_provided($attrOptionRepository, AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

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

    function it_creates_a_simple_select_product_value_even_if_provided_codes_have_different_case(
        $attrOptionRepository,
        AttributeOptionInterface $option,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option->getCode()->willReturn('foobar');

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.FOOBAR')->willReturn($option);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'FOOBAR'
        );

        $productValue->shouldReturnAnInstanceOf(OptionValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptionCode('foobar');
    }

    function it_throws_an_exception_if_option_does_not_exist($attrOptionRepository, AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'simple_select_attribute',
            'code',
            'The option does not exist',
            OptionValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
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
