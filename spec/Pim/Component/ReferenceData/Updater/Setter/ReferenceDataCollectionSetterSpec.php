<?php

namespace spec\Pim\Component\ReferenceData\Updater\Setter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
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
        AnotherCustomProductValue $productValue1
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

        $productValue1->getCustomMaterials()->willReturn(new ArrayCollection());
        $productValue1->addCustomMaterial($refData1)->shouldBeCalled();
        $productValue1->addCustomMaterial($refData2)->shouldBeCalled();

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue1);

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

    function it_throws_an_exception_if_product_value_method_is_not_implemented(
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $collection = new ArrayCollection();
        $collection->add($refData);

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $repositoryResolver->resolve('customMaterials')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData);

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue);

        $this->shouldThrow('LogicException')
            ->during('setAttributeData', [
                $product,
                $attribute,
                ['code' => 'shiny_metal'],
                ['locale' => $locale, 'scope' => $scope]
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
        ProductInterface $product3,
        AnotherCustomProductValue $productValue1,
        AnotherCustomProductValue $productValue2,
        AnotherCustomProductValue $productValue3
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
        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue2);
        $product3->getValue('custom_material', $locale, $scope)->willReturn($productValue3);

        $builder->addProductValue($product1, $attribute, $locale, $scope)
            ->shouldBeCalled()
            ->willReturn($productValue1);

        $products = [$product1, $product2, $product3];

        $existantCollection = new ArrayCollection();
        $existantCollection->add($refData1);

        $existantCollection2 = new ArrayCollection();
        $existantCollection2->add($refData1);
        $existantCollection2->add($refData2);

        $productValue1->getCustomMaterials()->willReturn(new ArrayCollection());
        $productValue2->getCustomMaterials()->willReturn($existantCollection);
        $productValue3->getCustomMaterials()->willReturn($existantCollection2);

        $productValue1->addCustomMaterial($refData1)->shouldBeCalled();
        $productValue1->addCustomMaterial($refData2)->shouldBeCalled();
        $productValue2->addCustomMaterial($refData1)->shouldBeCalled();
        $productValue2->addCustomMaterial($refData2)->shouldBeCalled();
        $productValue3->addCustomMaterial($refData1)->shouldBeCalled();
        $productValue3->addCustomMaterial($refData2)->shouldBeCalled();

        $productValue2->removeCustomMaterial($refData1)->shouldBeCalled();
        $productValue3->removeCustomMaterial($refData1)->shouldBeCalled();
        $productValue3->removeCustomMaterial($refData2)->shouldBeCalled();

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
    public function addCustomMaterial($refData)
    {
    }

    public function removeCustomMaterial($refData)
    {
    }

    public function getCustomMaterials()
    {
    }
}
