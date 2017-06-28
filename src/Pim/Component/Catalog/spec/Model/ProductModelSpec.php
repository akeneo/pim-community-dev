<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\TimestampableInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class ProductModelSpec extends ObjectBehavior
{
    function it_is_a_product_model()
    {
        $this->shouldHaveType(ProductModel::class);
        $this->shouldImplement(ProductModelInterface::class);
    }

    function it_is_an_entity_with_values()
    {
        $this->shouldImplement(EntityWithValuesInterface::class);
    }

    function it_is_a_timestampable_entity()
    {
        $this->shouldImplement(TimestampableInterface::class);
    }

    function it_is_a_versionable_entity()
    {
        $this->shouldImplement(VersionableInterface::class);
    }

    function it_is_a_category_aware_entity()
    {
        $this->shouldImplement(CategoryAwareInterface::class);
    }

    function it_has_an_identifier(ValueInterface $identifier, AttributeInterface $identifierAttribute)
    {
        $identifier->getData()->willReturn('foobar');
        $identifier->getAttribute()->willReturn($identifierAttribute);
        $identifier->getLocale()->willReturn(null);
        $identifier->getScope()->willReturn(null);

        $this->setIdentifier($identifier);

        $this->getIdentifier()->shouldReturn('foobar');
    }

    function it_adds_a_value(
        ValueCollectionInterface $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $attribute->getCode()->willReturn('foobar');
        $attribute->isUnique()->willReturn(false);

        $value->getAttribute()->willReturn($attribute);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $values->add($value)->shouldBeCalled();

        $this->addValue($value)->shouldReturn($this);
    }

    function it_removes_a_value(
        ValueCollectionInterface $values,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $this->setValues($values);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('foobar');
        $attribute->isUnique()->willReturn(false);

        $this->removeValue($value)->shouldReturn($this);
    }

    function it_gets_the_codes_of_the_product_model_categories(
        CategoryInterface $categorie
    ) {
        $this->addCategory($categorie);

        $categorie->getCode()->willReturn('foobar');

        $this->getCategoryCodes()->shouldReturn(['foobar']);
    }
}
