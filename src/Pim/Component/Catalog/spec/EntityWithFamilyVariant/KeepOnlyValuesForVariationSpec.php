<?php

namespace spec\Pim\Component\Catalog\EntityWithFamilyVariant;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AbstractAttribute;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class KeepOnlyValuesForVariationSpec extends ObjectBehavior
{
    function it_updates_root_product_models_values(
        ProductModelInterface $rootProductModel,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection,
        AbstractAttribute $description,
        AbstractAttribute $price,
        AbstractAttribute $width,
        AbstractAttribute $sku,
        AbstractAttribute $image,
        ValueCollectionInterface $valueCollection,
        \Iterator $valuesIterator,
        ValueInterface $descriptionValue,
        ValueInterface $priceValue,
        ValueInterface $widthValue,
        ValueInterface $skuValue,
        ValueInterface $imageValue
    ) {
        $rootProductModel->getVariationLevel()->willReturn(0);
        $rootProductModel->getFamilyVariant()->willReturn($familyVariant);

        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->toArray()->willReturn([
            $description, $price, $width
        ]);

        $description->__toString()->willReturn('description');
        $price->__toString()->willReturn('price');
        $width->__toString()->willReturn('width');
        $sku->__toString()->willReturn('sku');
        $image->__toString()->willReturn('image');

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

        $descriptionValue->getAttribute()->willReturn($description);
        $priceValue->getAttribute()->willReturn($price);
        $widthValue->getAttribute()->willReturn($width);
        $skuValue->getAttribute()->willReturn($sku);
        $imageValue->getAttribute()->willReturn($image);

        $valueCollection->removeByAttribute($sku)->shouldBeCalled();
        $valueCollection->removeByAttribute($image)->shouldBeCalled();

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
        ValueCollectionInterface $valueCollection,
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

        $description->__toString()->willReturn('description');
        $price->__toString()->willReturn('price');
        $width->__toString()->willReturn('width');
        $sku->__toString()->willReturn('sku');
        $image->__toString()->willReturn('image');
        $color->__toString()->willReturn('color');

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

        $descriptionValue->getAttribute()->willReturn($description);
        $priceValue->getAttribute()->willReturn($price);
        $widthValue->getAttribute()->willReturn($width);
        $skuValue->getAttribute()->willReturn($sku);
        $imageValue->getAttribute()->willReturn($image);
        $colorValue->getAttribute()->willReturn($color);

        $valueCollection->removeByAttribute($sku)->shouldBeCalled();
        $valueCollection->removeByAttribute($image)->shouldBeCalled();

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
        ValueCollectionInterface $valueCollection,
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

        $description->__toString()->willReturn('description');
        $price->__toString()->willReturn('price');
        $width->__toString()->willReturn('width');
        $sku->__toString()->willReturn('sku');
        $image->__toString()->willReturn('image');
        $size->__toString()->willReturn('size');

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

        $descriptionValue->getAttribute()->willReturn($description);
        $priceValue->getAttribute()->willReturn($price);
        $widthValue->getAttribute()->willReturn($width);
        $skuValue->getAttribute()->willReturn($sku);
        $imageValue->getAttribute()->willReturn($image);
        $sizeValue->getAttribute()->willReturn($size);

        $valueCollection->removeByAttribute($description)->shouldBeCalled();
        $valueCollection->removeByAttribute($price)->shouldBeCalled();
        $valueCollection->removeByAttribute($width)->shouldBeCalled();

        $variantProduct->setValues($valueCollection)->shouldBeCalled();

        $this->updateEntitiesWithFamilyVariant([$variantProduct]);
    }
}
