<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Structure\AttributeGroup;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;

class InMemoryAttributeGroupRepositorySpec extends ObjectBehavior
{
    function it_is_an_attribute_group_repository()
    {
        $this->shouldImplement(AttributeGroupRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

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

    function it_does_not_find_an_attribute_group_by_identifier()
    {
        $attribute = $this->createAttributeGroup('attribute_group_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findOneByIdentifier('attribute_group_2')->shouldReturn(null);
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

    function it_does_not_find_attribute_groups_by_criteria()
    {
        $attributeGroup = $this->createAttributeGroup('attribute_group_1');
        $this->beConstructedWith([$attributeGroup->getCode() => $attributeGroup]);

        $this->findBy(['code' => 'attribute_group_2'])->shouldReturn([]);
    }

    function it_throws_an_exception_if_saved_object_is_not_an_attribute_group(\StdClass $object)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The object argument should be a attribute group'))
            ->during('save', [$object]);
    }

    private function createAttributeGroup(string $code): AttributeGroupInterface
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode($code);

        return $attributeGroup;
    }
}
