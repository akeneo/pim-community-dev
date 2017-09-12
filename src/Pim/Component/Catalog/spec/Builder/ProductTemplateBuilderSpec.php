<?php

namespace spec\Pim\Component\Catalog\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;

class ProductTemplateBuilderSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $productBuilder, EntityWithFamilyValuesFillerInterface $productValuesFiller)
    {
        $this->beConstructedWith(
            $productBuilder,
            $productValuesFiller,
            'Pim\Bundle\CatalogBundle\Entity\ProductTemplate'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Builder\ProductTemplateBuilder');
    }

    function it_creates_product_templates()
    {
        $this->createProductTemplate()->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\ProductTemplate');
    }

    function it_adds_attributes_to_a_product_template(
        $productBuilder,
        $productValuesFiller,
        ProductTemplateInterface $template,
        AttributeInterface $name,
        ValueCollectionInterface $originalValues,
        ValueCollectionInterface $newValues,
        ProductInterface $product
    ) {
        $productBuilder->createProduct()->willReturn($product);

        $template->getValues()->willReturn($originalValues);
        $product->setValues($originalValues)->shouldBeCalled();

        $productBuilder->addAttribute($product, $name)->shouldBeCalled();
        $productValuesFiller->fillMissingValues($product)->shouldBeCalled();

        $product->getValues()->willReturn($newValues);

        $template->setValues($newValues)->shouldBeCalled();

        $this->addAttributes($template, [$name]);
    }

    function it_removes_attributes_from_a_product_template(
        ProductTemplateInterface $template,
        ValueInterface $nameValue,
        ValueInterface $colorValue,
        AttributeInterface $name,
        AttributeInterface $color,
        ValueCollectionInterface $values
    ) {
        $nameValue->getAttribute()->willReturn($name);
        $colorValue->getAttribute()->willReturn($color);

        $template->getValues()->willReturn($values);

        $values->removeByAttribute($name)->shouldBeCalled();

        $this->removeAttribute($template, $name);
    }
}
