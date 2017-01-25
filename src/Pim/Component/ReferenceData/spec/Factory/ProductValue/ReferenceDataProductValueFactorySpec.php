<?php

namespace spec\Pim\Component\ReferenceData\Factory\ProductValue;

use Acme\Bundle\AppBundle\Entity\Color;
use Acme\Bundle\AppBundle\Model\ProductValue;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataProductValueFactory;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Prophecy\Argument;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataProductValueFactorySpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver, ProductValue::class, 'pim_reference_data_catalog_simpleselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataProductValueFactory::class);
    }

    function it_supports_pim_reference_data_catalog_simpleselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(true);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(false);
    }

    function it_throws_an_exception_when_product_value_class_is_wrong($repositoryResolver)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The product value class "foobar" does not exist.'))
            ->during('__construct', [$repositoryResolver, 'foobar', 'pim_reference_data_catalog_simpleselect']);
    }

    function it_creates_an_empty_simple_select_reference_data_product_value(
        $repositoryResolver,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_simple_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_simpleselect');
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

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ''
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
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
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_simpleselect');
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

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ''
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
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
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve('color')->shouldBeCalled()->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'blue'])->shouldBeCalled()->willReturn($color);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'blue'
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
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
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('color');

        $repositoryResolver->resolve('color')->shouldBeCalled()->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'blue'])->shouldBeCalled()->willReturn($color);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'blue'
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveReferenceData($color);
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
