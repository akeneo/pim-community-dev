<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Acme\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataCollectionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Prophecy\Argument;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionValueFactorySpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $repositoryResolver)
    {
        $this->beConstructedWith($repositoryResolver, ReferenceDataCollectionValue::class, 'pim_reference_data_catalog_multiselect');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataCollectionValueFactory::class);
    }

    function it_supports_pim_reference_data_catalog_multiselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(true);
    }

    function it_creates_an_empty_reference_data_multi_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
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
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_reference_data_multi_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
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
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_reference_data_multi_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn($cotton);
        $silk->getCode()->willReturn('silk');
        $cotton->getCode()->willReturn('cotton');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['silk', 'cotton']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveReferenceData(['silk', 'cotton']);
    }

    function it_sorts_reference_data_multi_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn($cotton);
        $silk->getCode()->willReturn('silk');
        $cotton->getCode()->willReturn('cotton');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['silk', 'cotton']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveReferenceDataSorted(['cotton', 'silk']);
    }

    function it_creates_a_localizable_and_scopable_reference_data_multi_select_product_value(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn($cotton);
        $silk->getCode()->willReturn('silk');
        $cotton->getCode()->willReturn('cotton');

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['silk', 'cotton']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveReferenceData(['silk', 'cotton']);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $exception = InvalidPropertyTypeException::arrayExpected(
            'reference_data_multi_select_attribute',
            ReferenceDataCollectionValueFactory::class,
            true
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, true]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array_of_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'reference_data_multi_select_attribute',
            'array key "foo" expects a string as value, "array" given',
            ReferenceDataCollectionValueFactory::class,
            ['foo' => ['bar']]
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['foo' => ['bar']]]);
    }

    function it_throws_an_exception_when_reference_data_code_does_not_exist_with_inactive_ignore_unknown_data_option(
        $repositoryResolver,
        ReferenceDataRepositoryInterface $referenceDataRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'foobar'])->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'reference_data_multi_select_attribute',
            'reference data code',
            'The code of the reference data "fabrics" does not exist',
            ReferenceDataCollectionValueFactory::class,
            'foobar'
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['foobar'], false]);
    }

    function it_does_not_stop_when_provided_data_is_not_an_existing_reference_data_code_with_ignore_unknown_data_option_active(
        $repositoryResolver,
        AttributeInterface $attribute,
        Fabric $silk,
        ReferenceDataRepositoryInterface $referenceDataRepository
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $repositoryResolver->resolve('fabrics')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneBy(['code' => 'silk'])->willReturn($silk);

        $silk->getCode()->willReturn('silk');
        $referenceDataRepository->findOneBy(['code' => 'cotton'])->willReturn(null);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['silk', 'cotton'],
            true
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceDataCollectionValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldHaveOnlyOneReferenceData();
        $productValue->shouldHaveReferenceData(['silk']);
    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute'     => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable'     => function ($subject) {
                return null !== $subject->getLocaleCode();
            },
            'haveLocale'        => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'        => function ($subject) {
                return null !== $subject->getScopeCode();
            },
            'haveChannel'       => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'           => function ($subject) {
                return is_array($subject->getData()) && 0 === count($subject->getData());
            },
            'haveReferenceData' => function ($subject, $expected) {
                return empty(array_diff($subject->getData(), $expected))
                    && empty(array_diff($expected, $subject->getData()));
            },
            'haveReferenceDataSorted' => function ($subject, $expected) {
                $data = $subject->getData();
                if (count($data) !== count($expected)) {
                    return false;
                }

                for ($i = 0; $i < count($expected); $i++) {
                    if ($expected[$i] !== $data[$i]) {
                        return false;
                    }
                }

                return true;
            },
            'haveOnlyOneReferenceData' => function ($subject) {
                return 1 === count($subject->getData());
            },
        ];
    }
}
