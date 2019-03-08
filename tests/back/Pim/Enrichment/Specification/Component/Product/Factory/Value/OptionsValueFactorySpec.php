<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionsException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionsValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Prophecy\Argument;

class OptionsValueFactorySpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository, OptionsValue::class, 'pim_catalog_multiselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsValueFactory::class);
    }

    function it_supports_multiselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);
    }

    function it_creates_an_empty_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            []
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            []
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option1->getCode()->willReturn('foo');
        $option2->getCode()->willReturn('bar');

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar')->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptionCodes(['foo', 'bar']);
    }

    function it_sorts_options_in_a_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option1->getCode()->willReturn('foo');
        $option2->getCode()->willReturn('bar');

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar')->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptionCodesSorted(['bar', 'foo']);
    }

    function it_creates_a_localizable_and_scopable_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option1->getCode()->willReturn('foo');
        $option2->getCode()->willReturn('bar');

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar') ->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOptionCodes(['foo', 'bar']);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::arrayExpected(
            'multi_select_attribute',
            OptionsValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
    }

    function it_creates_a_multi_select_product_value_even_if_provided_code_has_a_different_case(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $option1->getCode()->willReturn('foo');
        $option2->getCode()->willReturn('bar');

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.FOO')->willReturn($option1);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.BAR')->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['FOO', 'BAR']
        );

        $productValue->shouldReturnAnInstanceOf(OptionsValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptionCodes(['foo', 'bar']);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array_of_strings(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'multi_select_attribute',
            'one of the options is not a string, "integer" given',
            OptionsValueFactory::class,
            [42]
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', [42]]);
    }

    function it_throws_an_exception_if_option_does_not_exist(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $bar
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar')->willReturn($bar);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn(null);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.baz')->willReturn(null);

        $exception = InvalidOptionsException::validEntityListCodesExpected(
            'multi_select_attribute',
            'codes',
            'The options do not exist',
            OptionsValueFactory::class,
            ['baz', 'foo']
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['foo', 'bar', 'baz']]);
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
                return is_array($subject->getData()) && empty($subject->getData());
            },
            'haveTheOptionCodes' => function ($subject, $expectedOptionCodes) {
                return empty(array_diff($subject->getData(), $expectedOptionCodes))
                    && empty(array_diff($expectedOptionCodes, $subject->getData()));
            },
            'haveTheOptionCodesSorted' => function ($subject, $expectedOptionCodes) {
                $data = $subject->getData();
                if (count($data) !== count($expectedOptionCodes)) {
                    return false;
                }

                for ($i = 0; $i < count($expectedOptionCodes); $i++) {
                    if ($expectedOptionCodes[$i] !== $data[$i]) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
