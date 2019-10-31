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
        $this->getIdentifierProperties()->shouldReturn(['attribute', 'code']);
    }

    function it_finds_one_attribute_option_by_identifier()
    {
        $hairColor = (new Attribute())->setCode('hair_color');
        $brownHairColor = $this->createAttributeOption('brown', $hairColor);
        $whiteHairColor = $this->createAttributeOption('white', $hairColor);

        $eyeColor = (new Attribute())->setCode('eye_color');
        $brownEyeColor = $this->createAttributeOption('brown', $eyeColor);

        $this->beConstructedWith(
            [
                $brownHairColor,
                $whiteHairColor,
                $brownEyeColor,
            ]
        );

        $this->findOneByIdentifier('hair_color.brown')->shouldReturn($brownHairColor);
        $this->findOneByIdentifier('Eye_Color.Brown')->shouldReturn($brownEyeColor);
    }

    function it_does_not_find_an_attribute_option_by_identifier()
    {
        $brownHairColor = $this->createAttributeOption('brown', (new Attribute())->setCode('hair_color'));

        $this->beConstructedWith([$brownHairColor]);

        $this->findOneByIdentifier('hair_color.white')->shouldReturn(null);
        $this->findOneByIdentifier('eye_color.brown')->shouldReturn(null);
        $this->findOneByIdentifier('eye_color.blue')->shouldReturn(null);
    }

    function it_saves_an_attribute_option()
    {
        $brownHairColor = $this->createAttributeOption('brown', (new Attribute())->setCode('hair_color'));

        $this->save($brownHairColor);

        $this->findOneByIdentifier('hair_color.brown')->shouldReturn($brownHairColor);
    }

    function it_finds_attribute_options_by_criteria()
    {
        $hairColor = (new Attribute())->setCode('hair_color');
        $brownHairColor = $this->createAttributeOption('brown', $hairColor);

        $eyeColor = (new Attribute())->setCode('eye_color');
        $blueEyeColor = $this->createAttributeOption('blue', $eyeColor);
        $brownEyeColor = $this->createAttributeOption('brown', $eyeColor);

        $this->beConstructedWith(
            [
                $brownHairColor,
                $blueEyeColor,
                $brownEyeColor,
            ]
        );

        $this->findBy(['code' => 'brown'])->shouldReturn([$brownHairColor, $brownEyeColor]);
        $this->findBy(['attribute' => $eyeColor])->shouldReturn([$blueEyeColor, $brownEyeColor]);
        $this->findBy(['attribute' => $eyeColor, 'code' => 'brown'])->shouldReturn([$brownEyeColor]);
    }

    function it_does_not_find_attribute_options_by_criteria()
    {
        $eyeColor = (new Attribute())->setCode('eye_color');
        $hairColor = (new Attribute())->setCode('hair_color');
        $brownHairColor = $this->createAttributeOption('brown', $hairColor);

        $this->beConstructedWith([$brownHairColor]);

        $this->findBy(['attribute' => $eyeColor, 'code' => 'brown'])->shouldReturn([]);
        $this->findBy(['attribute' => $hairColor, 'code' => 'white'])->shouldReturn([]);
    }

    function it_throws_an_exception_if_saved_object_is_not_an_attribute_option(\StdClass $object)
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$object]);
    }

    function it_finds_attribute_options_by_attribute_and_codes()
    {
        $eyeColor = (new Attribute())->setCode('eye_color');
        $blueEyeColor = $this->createAttributeOption('blue', $eyeColor);
        $brownEyeColor = $this->createAttributeOption('brown', $eyeColor);

        $this->beConstructedWith(
            [
                $blueEyeColor,
                $brownEyeColor,
            ]
        );

        $this
            ->findCodesByIdentifiers('eye_color', ['green'])
            ->shouldReturn([]);

        $this
            ->findCodesByIdentifiers('eye_color', ['blue'])
            ->shouldReturn([['code' => 'blue']]);

        $this
            ->findCodesByIdentifiers('eye_color', ['blue', 'brown'])
            ->shouldReturn([['code' => 'blue'], ['code' => 'brown']]);
    }

    private function createAttributeOption(string $code, AttributeInterface $attribute): AttributeOptionInterface
    {
        $attributeOption = new AttributeOption();
        $attributeOption->setCode($code);
        $attributeOption->setAttribute($attribute);

        return $attributeOption;
    }
}
