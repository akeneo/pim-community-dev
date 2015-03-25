<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\ReferenceDataDenormalizer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class ReferenceDataSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        ReferenceDataDenormalizer $refDataDenormalizer
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $refDataDenormalizer,
            ['pim_reference_data_simpleselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
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
        $refDataDenormalizer,
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

        $refDataDenormalizer->denormalize(
            ['code' => 'shiny_metal'],
            '',
            null,
            ['attribute' => $attribute]
        )->willReturn($refData);

        $this->setAttributeData(
            $product,
            $attribute,
            ['code' => 'shiny_metal'],
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

    function it_throws_an_exception_if_reference_data_does_not_exist(
        $attrValidatorHelper,
        $refDataDenormalizer,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $refDataDenormalizer->denormalize(
            ['code' => 'hulk_retriever'],
            '',
            null,
            ['attribute' => $attribute]
        )->willReturn(null);

        $this->shouldThrow('LogicException')->during(
            'setAttributeData',
            [
                $product,
                $attribute,
                ['code' => 'hulk_retriever'],
                ['locale' => 'fr_FR', 'scope' => 'mobile']
            ]
        );
    }

    function it_throws_an_exception_if_product_value_method_is_not_implemented(
        $refDataDenormalizer,
        ReferenceDataInterface $refData,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('custom_material');
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $refDataDenormalizer->denormalize(
            ['code' => 'shiny_metal'],
            '',
            null,
            ['attribute' => $attribute]
        )->willReturn($refData);

        $product->getValue('custom_material', $locale, $scope)->willReturn($productValue);

        $this->shouldThrow('LogicException')
            ->during('setAttributeData', [
                $product,
                $attribute,
                ['code' => 'shiny_metal'],
                ['locale' => $locale, 'scope' => $scope]
            ]);
    }

    function it_sets_reference_data_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $refDataDenormalizer,
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
        $attribute->getReferenceDataName()->willReturn('customMaterials');

        $refDataDenormalizer->denormalize(
            ['code' => 'shiny_metal'],
            '',
            null,
            ['attribute' => $attribute]
        )->willReturn($refData);


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
            $this->setAttributeData($product, $attribute, ['code' => 'shiny_metal'], ['locale' => $locale, 'scope' => $scope]);
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
    public function setCustomMaterial($refData)
    {

    }
}
