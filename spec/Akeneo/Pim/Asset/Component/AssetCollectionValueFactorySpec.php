<?php

namespace spec\Akeneo\Pim\Asset\Component;

use Acme\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Asset\Component\AssetCollectionValueFactory;
use Prophecy\Argument;

class AssetCollectionValueFactorySpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver, ReferenceDataCollectionValue::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionValueFactory::class);
    }

    function it_supports_asset_collection_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_assets_collection')->shouldReturn(true);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(false);
    }

    function it_creates_an_empty_assets_collection_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('assets_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_assets_collection_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('assets_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_assets_collection_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn($cotton);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['silk', 'cotton']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('assets_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveReferenceData([$silk, $cotton]);
    }

    function it_creates_a_localizable_and_scopable_assets_collection_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn($cotton);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['silk', 'cotton']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('assets_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveReferenceData([$silk, $cotton]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $exception = InvalidPropertyTypeException::arrayExpected(
            'assets_collection_attribute',
            AssetCollectionValueFactory::class,
            true
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, true]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array_of_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'assets_collection_attribute',
            'array key "foo" expects a string as value, "array" given',
            AssetCollectionValueFactory::class,
            ['foo' => ['bar']]
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['foo' => ['bar']]]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_existing_reference_data_code(
        $repositoryResolver,
        ReferenceDataRepositoryInterface $referenceDataRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('assets_collection_attribute');
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'foobar'])->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'assets_collection_attribute',
            'reference data code',
            'The code of the reference data "fabrics" does not exist',
            AssetCollectionValueFactory::class,
            'foobar'
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['foobar']]);
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
                return is_array($subject->getData()) && 0 === count($subject->getData());
            },
            'haveReferenceData' => function ($subject, $expected) {
                $fabrics = $subject->getData();

                $hasFabrics = false;
                foreach ($fabrics as $fabric) {
                    $hasFabrics = in_array($fabric, $expected);
                }

                return $hasFabrics;
            },
        ];
    }
}
