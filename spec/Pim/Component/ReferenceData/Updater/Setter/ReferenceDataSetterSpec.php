<?php

namespace spec\Pim\Component\ReferenceData\Updater\Setter;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Prophecy\Argument;

class ReferenceDataSetterSpec extends ObjectBehavior
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
            ['pim_reference_data_simpleselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_reference_data_attributes(
        AttributeInterface $refDataAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $refDataAttribute->getAttributeType()->willReturn('pim_reference_data_simpleselect');
        $this->supportsAttribute($refDataAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData,
        AttributeInterface $attribute,
        ProductInterface $product,
        CustomProductValue $productValue1
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getReferenceDataName()->willReturn('customMaterials');
        $attribute->getCode()->willReturn('custom_material');

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue1);

        $repositoryResolver->resolve('customMaterials')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData);

        $this->setAttributeData(
            $product,
            $attribute,
            'shiny_metal',
            ['locale' => $locale, 'scope' => $scope]
        );
    }

    function it_throws_an_exception_if_data_is_a_string(
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $this->shouldThrow('InvalidArgumentException')->during('setAttributeData', [
            $product, $attribute, ['shiny_metal'], ['locale' => 'fr_FR', 'scope' => 'mobile']
        ]);
    }

    function it_throws_an_exception_if_reference_data_does_not_exist(
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('customMaterials');
        $attribute->getCode()->willReturn('lace_fabric');
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $repositoryResolver->resolve('customMaterials')->willReturn($repository);
        $repository->findOneBy(['code' => 'hulk_retriever'])->willReturn(null);

        $exception = InvalidArgumentException::validEntityCodeExpected(
            'lace_fabric',
            'code',
            'No reference data "customMaterials" with code "hulk_retriever" has been found',
            'setter',
            'reference data',
            'hulk_retriever'
        );

        $this->shouldThrow($exception)->during(
            'setAttributeData',
            [
                $product,
                $attribute,
                'hulk_retriever',
                ['locale' => 'fr_FR', 'scope' => 'mobile']
            ]
        );
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

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('notImplemented');

        $repositoryResolver->resolve('notImplemented')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData);

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue);

        $this->shouldThrow(new \LogicException('ProductValue method "setNotImplemented" is not implemented'))
            ->during('setAttributeData', [
                $product,
                $attribute,
                'shiny_metal',
                ['locale' => $locale, 'scope' => $scope]
            ]);
    }

    function it_sets_reference_data_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        CustomProductValue $productValue1,
        CustomProductValue $productValue2,
        CustomProductValue $productValue3
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterial');

        $repositoryResolver->resolve('customMaterial')->willReturn($repository);
        $repository->findOneBy(['code' => 'shiny_metal'])->willReturn($refData);

        $product1->getValue('custom_material', $locale, $scope)->willReturn(null);
        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue2);
        $product3->getValue('custom_material', $locale, $scope)->willReturn($productValue3);

        $builder->addProductValue($product1, $attribute, $locale, $scope)
            ->shouldBeCalled()
            ->willReturn($productValue1);

        $products = [$product1, $product2, $product3];

        $productValue1->setCustomMaterial($refData)->shouldBeCalled();
        $productValue2->setCustomMaterial($refData)->shouldBeCalled();
        $productValue3->setCustomMaterial($refData)->shouldBeCalled();

        foreach ($products as $product) {
            $this->setAttributeData($product, $attribute, 'shiny_metal', ['locale' => $locale, 'scope' => $scope]);
        }
    }

    function it_allows_setting_reference_data_to_null(
        $builder,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        CustomProductValue $productValue1,
        CustomProductValue $productValue2
    ) {
        $locale = 'en_US';
        $scope = 'ecommerce';

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $product1->getValue('custom_material', $locale, $scope)->willReturn(null);
        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue2);

        $builder->addProductValue($product1, $attribute, $locale, $scope)
            ->shouldBeCalled()
            ->willReturn($productValue1);

        $products = [$product1, $product2];

        $productValue1->setCustomMaterial(null)->shouldBeCalled();
        $productValue2->setCustomMaterial(null)->shouldBeCalled();

        foreach ($products as $product) {
            $this->setAttributeData($product, $attribute, null, ['locale' => $locale, 'scope' => $scope]);
        }
    }
}

class CustomProductValue extends AbstractProductValue
{
    public function setCustomMaterial(ReferenceDataInterface $refData = null)
    {
    }
}
