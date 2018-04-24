<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Structure\Attribute;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class InMemoryAttributeRepositorySpec extends ObjectBehavior
{
    function it_is_an_attribute_repository()
    {
        $this->shouldImplement(AttributeRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_returns_an_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_one_attribute_by_identifier()
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findOneByIdentifier('attribute_1')->shouldReturn($attribute);
    }

    function it_does_not_find_an_attribute_by_identifier()
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findOneByIdentifier('attribute_2')->shouldReturn(null);
    }

    function it_saves_an_attribute()
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->save($attribute);

        $this->findOneByIdentifier('attribute_1')->shouldReturn($attribute);
    }

    function it_finds_attributes_by_criteria()
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findBy(['code' => 'attribute_1'])->shouldReturn([$attribute]);
    }

    function it_does_not_find_attributes_by_criteria()
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findBy(['code' => 'attribute_2'])->shouldReturn([]);
    }

    function it_throws_an_exception_if_saved_object_is_not_an_attribute(\StdClass $object)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The object argument should be a attribute'))
            ->during('save', [$object]);
    }

    private function createAttribute(string $code): AttributeInterface
    {
        $attribute = new Attribute();
        $attribute->setCode($code);

        return $attribute;
    }
}
