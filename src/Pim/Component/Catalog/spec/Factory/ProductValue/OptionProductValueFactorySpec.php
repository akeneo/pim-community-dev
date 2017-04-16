<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\OptionProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\ProductValue\ScalarProductValue;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Prophecy\Argument;

class OptionProductValueFactorySpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attrOptionRepository)
    {
        $this->beConstructedWith($attrOptionRepository, ScalarProductValue::class, 'pim_catalog_simpleselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionProductValueFactory::class);
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

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
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

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
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

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
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

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
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
            OptionProductValueFactory::class,
            true
        );

        $arrayException = InvalidPropertyTypeException::stringExpected(
            'simple_select_attribute',
            OptionProductValueFactory::class,
            []
        );

        $this
            ->shouldThrow($booleanException)
            ->during('create', [$attribute, 'ecommerce', 'en_US', true]);

        $this
            ->shouldThrow($arrayException)
            ->during('create', [$attribute, 'ecommerce', 'en_US', []]);
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
            OptionProductValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
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
