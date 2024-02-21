<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class AttributeSetterSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_text', 'pim_catalog_textarea']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
    }

    function it_supports_text_attributes(
        AttributeInterface $textAttribute,
        AttributeInterface $textareaAttribute,
        AttributeInterface $numberAttribute
    ) {
        $textAttribute->getType()->willReturn('pim_catalog_text');
        $this->supportsAttribute($textAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(true);

        $numberAttribute->getType()->willReturn('pim_catalog_number');
        $this->supportsAttribute($numberAttribute)->shouldReturn(false);
    }

    function it_sets_attribute_data_text_value_to_a_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 'data';

        $builder->addOrReplaceValue($product, $attribute, $locale, $scope, $data);

        $this->setAttributeData($product, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
