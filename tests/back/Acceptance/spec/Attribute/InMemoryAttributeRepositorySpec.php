<?php

namespace spec\Akeneo\Test\Acceptance\Attribute;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;

class InMemoryAttributeRepositorySpec extends ObjectBehavior
{
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

    private function createAttribute(string $code): AttributeInterface
    {
        $attribute = new Attribute();
        $attribute->setCode($code);

        return $attribute;
    }
}
