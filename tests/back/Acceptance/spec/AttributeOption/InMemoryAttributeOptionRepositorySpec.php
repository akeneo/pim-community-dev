<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

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

    function it_finds_attribute_options_by_attribute_and_codes()
    {
        $attribute = new Attribute();
        $attributeOption1 = $this->createAttributeOption('attribute_option_1', $attribute);
        $attributeOption2 = $this->createAttributeOption('attribute_option_2', $attribute);
        $attributeOption3 = $this->createAttributeOption('attribute_option_3', $attribute);

        $attribute2 = (new Attribute())->setCode('test');
        $attributeOption4 = $this->createAttributeOption('attribute_option_4', $attribute2);

        $this->beConstructedWith([
            $attributeOption1->getCode() => $attributeOption1,
            $attributeOption2->getCode() => $attributeOption2,
            $attributeOption3->getCode() => $attributeOption3,
            $attributeOption4->getCode() => $attributeOption4,
        ]);

        $this
            ->findCodesByIdentifiers($attribute->getCode(), ['attribute_option_1', 'attribute_option_2'])
            ->shouldReturn([$attributeOption1->getCode(), $attributeOption2->getCode()]);

        $this
            ->findCodesByIdentifiers($attribute2->getCode(), ['attribute_option_1'])
            ->shouldReturn([]);

        $this
            ->findCodesByIdentifiers($attribute2->getCode(), ['attribute_option_4'])
            ->shouldReturn([$attributeOption4->getCode()]);
    }

    private function createAttributeOption(string $code, AttributeInterface $attribute): AttributeOptionInterface
    {
        $attributeOption = new AttributeOption();
        $attributeOption->setCode($code);
        $attributeOption->setAttribute($attribute);

        return $attributeOption;
    }
}
