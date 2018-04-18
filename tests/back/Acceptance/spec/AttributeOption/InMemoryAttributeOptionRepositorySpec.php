<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AttributeOption;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

class InMemoryAttributeOptionRepositorySpec extends ObjectBehavior
{
    function it_returns_an_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_one_attribute_option_by_identifier()
    {
        $attribute = new Attribute();
        $attributeOption = $this->createAttributeOption('attribute_option_1', $attribute);
        $this->beConstructedWith([$attributeOption->getCode() => $attributeOption]);

        $this->findOneByIdentifier('attribute_option_1')->shouldReturn($attributeOption);
    }

    function it_saves_an_attribute_option()
    {
        $attribute = new Attribute();
        $attributeOption = $this->createAttributeOption('attribute_option_1', $attribute);
        $this->save($attributeOption);

        $this->findOneByIdentifier('attribute_option_1')->shouldReturn($attributeOption);
    }

    function it_finds_attribute_options_by_criteria()
    {
        $attribute = new Attribute();
        $attributeOption = $this->createAttributeOption('attribute_option_1', $attribute);
        $this->beConstructedWith([$attributeOption->getCode() => $attributeOption]);

        $this->findBy(['code' => 'attribute_option_1'])->shouldReturn([$attributeOption]);
    }

    private function createAttributeOption(string $code, AttributeInterface $attribute): AttributeOptionInterface
    {
        $attributeOption = new AttributeOption();
        $attributeOption->setCode($code);
        $attributeOption->setAttribute($attribute);

        return $attributeOption;
    }
}
