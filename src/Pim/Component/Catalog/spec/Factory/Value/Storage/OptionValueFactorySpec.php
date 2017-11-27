<?php

namespace spec\Pim\Component\Catalog\Factory\Value\Storage;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\Value\Storage\OptionValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use Prophecy\Argument;

class OptionValueFactorySpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attrOptionRepository)
    {
        $this->beConstructedWith($attrOptionRepository, ScalarValue::class, 'pim_catalog_simpleselect');
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

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
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

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_simple_select_product_value(
        $attrOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn($option);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOption($option);
    }

    function it_creates_a_localizable_and_scopable_simple_select_product_value(
        $attrOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn($option);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOption($option);
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

    function it_returns_an_empty_product_value_if_option_does_not_exist(
        $attrOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attrOptionRepository->findOneByIdentifier('simple_select_attribute.foobar')->willReturn(null);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();

    }

    public function getMatchers()
    {
        return [
            'haveAttribute'  => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'  => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'     => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'     => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'    => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'        => function ($subject) {
                return null === $subject->getData();
            },
            'haveTheOption'  => function ($subject, $option) {
                return $option === $subject->getData();
            },
        ];
    }
}
