<?php

namespace spec\Pim\Component\ReferenceData\Updater\Setter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Prophecy\Argument;

class ReferenceDataCollectionSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        ReferenceDataRepositoryResolverInterface $repositoryResolver
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $repositoryResolver,
            ['pim_reference_data_multiselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_reference_data_collection_attributes(
        AttributeInterface $refDataCollectionAttribute,
        AttributeInterface $refDataAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $refDataCollectionAttribute->getAttributeType()->willReturn('pim_reference_data_multiselect');
        $this->supportsAttribute($refDataCollectionAttribute)->shouldReturn(true);

        $refDataAttribute->getAttributeType()->willReturn('pim_reference_data_simpleselect');
        $this->supportsAttribute($refDataAttribute)->shouldReturn(false);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData1,
        ReferenceDataInterface $refData2,
        AttributeInterface $attribute,
        ProductInterface $product,
        AnotherCustomProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $collection = new ArrayCollection();
        $collection->add($refData1);
        $collection->add($refData2);

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getReferenceDataName()->willReturn('customMaterials');
        $attribute->getCode()->willReturn('custom_material');

        $productValue->getCustomMaterials()->willReturn($collection);

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue);
        $product->removeValue($productValue)->shouldBeCalled()->willReturn($product);

        $repositoryResolver->resolve('customMaterials')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData1);
        $repository->findOneBy(['code' => 'cold_metal'])->willReturn($refData2);

        $this->setAttributeData(
            $product,
            $attribute,
            ['shiny_metal', 'cold_metal'],
            ['locale' => $locale, 'scope' => $scope]
        );
    }

    function it_throws_an_exception_if_data_is_not_an_array(
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $this->shouldThrow('InvalidArgumentException')->during('setAttributeData', [
            $product, $attribute, 'shiny_metal', ['locale' => 'fr_FR', 'scope' => 'mobile']
        ]);
    }

    function it_sets_reference_data_collection_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData1,
        ReferenceDataInterface $refData2,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValueInterface $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $collection = new ArrayCollection();
        $collection->add($refData1);
        $collection->add($refData2);

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $repositoryResolver->resolve('customMaterials')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData1);
        $repository->findOneBy(['code' => 'cold_metal'])->willReturn($refData2);

        $product1->getValue('custom_material', $locale, $scope)->willReturn(null);
        $product1->removeValue($productValue)->shouldNotBeCalled();
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, [$refData1, $refData2])
            ->shouldBeCalled()
            ->willReturn($product1);

        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue);
        $product2->removeValue($productValue)->shouldBeCalled()->willReturn($product2);
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, [$refData1, $refData2])
            ->shouldBeCalled()
            ->willReturn($product2);

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->setAttributeData(
                $product,
                $attribute,
                ['shiny_metal', 'cold_metal'],
                ['locale' => $locale, 'scope' => $scope]
            );
        }
    }
}

class AnotherCustomProductValue extends AbstractProductValue
{
    public function getCustomMaterials()
    {
    }
}
