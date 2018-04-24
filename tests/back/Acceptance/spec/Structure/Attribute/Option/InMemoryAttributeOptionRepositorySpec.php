<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Structure\Attribute\Option;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;

class InMemoryAttributeOptionRepositorySpec extends ObjectBehavior
{
    function it_is_an_attribute_option_repository()
    {
        $this->shouldImplement(AttributeOptionRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

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

    function it_does_not_find_an_attribute_option_by_identifier()
    {
        $attribute = new Attribute();
        $attributeOption = $this->createAttributeOption('attribute_option_1', $attribute);
        $this->beConstructedWith([$attributeOption->getCode() => $attributeOption]);

        $this->findOneByIdentifier('attribute_option_2')->shouldReturn(null);
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

    function it_does_not_find_attribute_options_by_criteria()
    {
        $attribute = new Attribute();
        $attributeOption = $this->createAttributeOption('attribute_option_1', $attribute);
        $this->beConstructedWith([$attributeOption->getCode() => $attributeOption]);

        $this->findBy(['code' => 'attribute_option_2'])->shouldReturn([]);
    }

    function it_throws_an_exception_if_saved_object_is_not_an_attribute_option(\StdClass $object)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The object argument should be a attribute option'))
            ->during('save', [$object]);
    }

    private function createAttributeOption(string $code, AttributeInterface $attribute): AttributeOptionInterface
    {
        $attributeOption = new AttributeOption();
        $attributeOption->setCode($code);
        $attributeOption->setAttribute($attribute);

        return $attributeOption;
    }
}
