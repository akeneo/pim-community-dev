<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class KeepOnlyValuesForVariationSpec extends ObjectBehavior
{
    function it_updates_root_product_models_values(
        ProductModelInterface $rootProductModel,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection,
        \Iterator $commonAttributesIterator,
        AbstractAttribute $description,
        AbstractAttribute $price,
        AbstractAttribute $width,
        AbstractAttribute $sku,
        AbstractAttribute $image,
        WriteValueCollection $valueCollection,
        \Iterator $valuesIterator,
        ValueInterface $descriptionValue,
        ValueInterface $priceValue,
        ValueInterface $widthValue,
        ValueInterface $skuValue,
        ValueInterface $imageValue
    ) {
        $rootProductModel->getVariationLevel()->willReturn(0);
        $rootProductModel->getFamilyVariant()->willReturn($familyVariant);

        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');
        $width->getCode()->willReturn('width');
        $sku->getCode()->willReturn('sku');
        $image->getCode()->willReturn('image');

        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->getIterator()->willReturn($commonAttributesIterator);
        $commonAttributesIterator->rewind()->shouldBeCalled();
        $commonAttributesIterator->valid()->willReturn(true, true, true, false);
        $commonAttributesIterator->current()->willReturn(
            $description, $price, $width
        );
        $commonAttributesIterator->next()->shouldBeCalled();

        $rootProductModel->getValues()->willReturn($valueCollection);
        $valueCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, true, true, true, false);
        $valuesIterator->current()->willReturn(
            $descriptionValue,
            $priceValue,
            $widthValue,
            $skuValue,
            $imageValue
        );
        $valuesIterator->next()->shouldBeCalled();

        $descriptionValue->getAttributeCode()->willReturn('description');
        $priceValue->getAttributeCode()->willReturn('price');
        $widthValue->getAttributeCode()->willReturn('width');
        $skuValue->getAttributeCode()->willReturn('sku');
        $imageValue->getAttributeCode()->willReturn('image');

        $valueCollection->removeByAttributeCode('sku')->shouldBeCalled();
        $valueCollection->removeByAttributeCode('image')->shouldBeCalled();

        $rootProductModel->setValues($valueCollection)->shouldBeCalled();

        $this->updateEntitiesWithFamilyVariant([$rootProductModel]);
    }

    function it_updates_sub_product_models_values(
        ProductModelInterface $subProductModel,
        FamilyVariantInterface $familyVariant,
        AbstractAttribute $description,
        AbstractAttribute $price,
        AbstractAttribute $width,
        AbstractAttribute $sku,
        AbstractAttribute $image,
        AbstractAttribute $color,
        WriteValueCollection $valueCollection,
        \Iterator $valuesIterator,
        ValueInterface $descriptionValue,
        ValueInterface $priceValue,
        ValueInterface $widthValue,
        ValueInterface $skuValue,
        ValueInterface $imageValue,
        ValueInterface $colorValue,
        VariantAttributeSetInterface $attributeSet,
        ArrayCollection $attributes,
        ArrayCollection $axes
    ) {
        $subProductModel->getVariationLevel()->willReturn(1);
        $subProductModel->getFamilyVariant()->willReturn($familyVariant);

        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);

        $attributeSet->getAttributes()->willReturn($attributes);
        $attributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$description, $price, $width]);
        $axes->toArray()->willReturn([$color]);

        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');
        $width->getCode()->willReturn('width');
        $sku->getCode()->willReturn('sku');
        $image->getCode()->willReturn('image');
        $color->getCode()->willReturn('color');

        $subProductModel->getValues()->willReturn($valueCollection);

        $valueCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, true, true, true, false);
        $valuesIterator->current()->willReturn(
            $descriptionValue,
            $priceValue,
            $widthValue,
            $skuValue,
            $imageValue,
            $colorValue
        );
        $valuesIterator->next()->shouldBeCalled();

        $descriptionValue->getAttributeCode()->willReturn('description');
        $priceValue->getAttributeCode()->willReturn('price');
        $widthValue->getAttributeCode()->willReturn('width');
        $skuValue->getAttributeCode()->willReturn('sku');
        $imageValue->getAttributeCode()->willReturn('image');
        $colorValue->getAttributeCode()->willReturn('color');

        $valueCollection->removeByAttributeCode('sku')->shouldBeCalled();
        $valueCollection->removeByAttributeCode('image')->shouldBeCalled();

        $subProductModel->setValues($valueCollection)->shouldBeCalled();

        $this->updateEntitiesWithFamilyVariant([$subProductModel]);
    }

    function it_updates_variant_products_values(
        ProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        AbstractAttribute $description,
        AbstractAttribute $price,
        AbstractAttribute $width,
        AbstractAttribute $sku,
        AbstractAttribute $image,
        AbstractAttribute $size,
        WriteValueCollection $valueCollection,
        \Iterator $valuesIterator,
        ValueInterface $descriptionValue,
        ValueInterface $priceValue,
        ValueInterface $widthValue,
        ValueInterface $skuValue,
        ValueInterface $imageValue,
        ValueInterface $sizeValue,
        VariantAttributeSetInterface $attributeSet,
        ArrayCollection $attributes,
        ArrayCollection $axes
    ) {
        $variantProduct->getVariationLevel()->willReturn(2);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $familyVariant->getVariantAttributeSet(2)->willReturn($attributeSet);

        $attributeSet->getAttributes()->willReturn($attributes);
        $attributeSet->getAxes()->willReturn($axes);

        $attributes->toArray()->willReturn([$sku, $image]);
        $axes->toArray()->willReturn([$size]);

        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');
        $width->getCode()->willReturn('width');
        $sku->getCode()->willReturn('sku');
        $image->getCode()->willReturn('image');
        $size->getCode()->willReturn('size');

        $variantProduct->getValues()->willReturn($valueCollection);

        $valueCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, true, true, true, false);
        $valuesIterator->current()->willReturn(
            $descriptionValue,
            $priceValue,
            $widthValue,
            $skuValue,
            $imageValue,
            $sizeValue
        );
        $valuesIterator->next()->shouldBeCalled();

        $descriptionValue->getAttributeCode()->willReturn('description');
        $priceValue->getAttributeCode()->willReturn('price');
        $widthValue->getAttributeCode()->willReturn('width');
        $skuValue->getAttributeCode()->willReturn('sku');
        $imageValue->getAttributeCode()->willReturn('image');
        $sizeValue->getAttributeCode()->willReturn('size');

        $valueCollection->removeByAttributeCode('description')->shouldBeCalled();
        $valueCollection->removeByAttributeCode('price')->shouldBeCalled();
        $valueCollection->removeByAttributeCode('width')->shouldBeCalled();

        $variantProduct->setValues($valueCollection)->shouldBeCalled();

        $this->updateEntitiesWithFamilyVariant([$variantProduct]);
    }
}
