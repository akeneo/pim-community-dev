<?php

namespace spec\Pim\Component\ReferenceData\Factory\Value;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\ReferenceData\Factory\Value\ReferenceDataValueFactory;
use Pim\Component\ReferenceData\Value\ReferenceDataValue;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Prophecy\Argument;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataValueFactorySpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver, ReferenceDataValue::class, 'pim_reference_data_catalog_simpleselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataValueFactory::class);
    }

    function it_supports_pim_reference_data_catalog_simpleselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(true);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(false);
    }

    function it_creates_an_empty_simple_select_reference_data_product_value(
        $repositoryResolver,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_reference_data_simple_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_simple_select_reference_data_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Color $color,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve('color')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'blue'])->willReturn($color);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'blue'
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveReferenceData($color);
    }

    function it_creates_a_localizable_and_scopable_reference_data_simple_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Color $color,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve('color')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'blue'])->willReturn($color);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'blue'
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveReferenceData($color);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $exception = InvalidPropertyTypeException::stringExpected(
            'reference_data_simple_select_attribute',
            ReferenceDataValueFactory::class,
            []
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, []]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_existing_reference_data_code(
        $repositoryResolver,
        ReferenceDataRepositoryInterface $referenceDataRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve('color')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'foobar'])->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'reference_data_simple_select_attribute',
            'reference data code',
            'The code of the reference data "color" does not exist',
            ReferenceDataValueFactory::class,
            'foobar'
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, 'foobar']);
    }

    public function getMatchers()
    {
        return [
            'haveAttribute'     => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'     => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'        => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'        => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'       => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'           => function ($subject) {
                return null === $subject->getData();
            },
            'haveReferenceData' => function ($subject, $expected) {
                return $expected === $subject->getData();
            }
        ];
    }
}
