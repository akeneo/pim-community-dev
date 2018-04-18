<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AttributeGroup;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Component\Catalog\Model\AttributeGroupInterface;

class InMemoryAttributeGroupRepositorySpec extends ObjectBehavior
{
    function it_returns_an_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_one_attribute_group_by_identifier()
    {
        $attributeGroup = $this->createAttributeGroup('attribute_group_1');
        $this->beConstructedWith([$attributeGroup->getCode() => $attributeGroup]);

        $this->findOneByIdentifier('attribute_group_1')->shouldReturn($attributeGroup);
    }

    function it_saves_an_attribute_group()
    {
        $attributeGroup = $this->createAttributeGroup('attribute_group_1');
        $this->save($attributeGroup);

        $this->findOneByIdentifier('attribute_group_1')->shouldReturn($attributeGroup);
    }

    function it_finds_attribute_groups_by_criteria()
    {
        $attributeGroup = $this->createAttributeGroup('attribute_group_1');
        $this->beConstructedWith([$attributeGroup->getCode() => $attributeGroup]);

        $this->findBy(['code' => 'attribute_group_1'])->shouldReturn([$attributeGroup]);
    }

    private function createAttributeGroup(string $code): AttributeGroupInterface
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode($code);

        return $attributeGroup;
    }
}
