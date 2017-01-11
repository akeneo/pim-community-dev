<?php

namespace spec\Pim\Component\ReferenceData\Updater\Setter;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
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
        ProductValueInterface $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getReferenceDataName()->willReturn('customMaterials');
        $attribute->getCode()->willReturn('custom_material');

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue);
        $product->removeValue($productValue)->shouldBeCalled()->willReturn($product);

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

    function it_sets_reference_data_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataInterface $refData,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValueInterface $productValue
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
        $product1->removeValue($productValue)->shouldNotBeCalled();
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, $refData)
            ->shouldBeCalled()
            ->willReturn($productValue);

        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue);
        $product2->removeValue($productValue)->shouldBeCalled()->willReturn($product2);
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, $refData)
            ->shouldBeCalled()
            ->willReturn($productValue);

        $products = [$product1, $product2];
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
        ProductValueInterface $productValue
    ) {
        $locale = 'en_US';
        $scope = 'ecommerce';

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $product1->getValue('custom_material', $locale, $scope)->willReturn(null);
        $product1->removeValue($productValue)->shouldNotBeCalled();
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, null)
            ->shouldBeCalled()
            ->willReturn($productValue);

        $product2->getValue('custom_material', $locale, $scope)->willReturn($productValue);
        $product2->removeValue($productValue)->shouldBeCalled()->willReturn($product2);
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, null)
            ->shouldBeCalled()
            ->willReturn($productValue);

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->setAttributeData($product, $attribute, null, ['locale' => $locale, 'scope' => $scope]);
        }
    }
}
