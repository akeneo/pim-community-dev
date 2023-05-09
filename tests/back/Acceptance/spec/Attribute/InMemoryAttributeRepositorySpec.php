<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class InMemoryAttributeRepositorySpec extends ObjectBehavior
{
    public function it_is_an_attribute_repository(): void
    {
        $this->shouldImplement(AttributeRepositoryInterface::class);
    }

    public function it_is_a_saver(): void
    {
        $this->shouldImplement(SaverInterface::class);
    }

    public function it_returns_an_identifier_property(): void
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    public function it_finds_one_attribute_by_identifier(): void
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findOneByIdentifier('attribute_1')->shouldReturn($attribute);
    }

    public function it_does_not_find_an_attribute_by_identifier(): void
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findOneByIdentifier('attribute_2')->shouldReturn(null);
    }

    public function it_saves_an_attribute(): void
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->save($attribute);

        $this->findOneByIdentifier('attribute_1')->shouldReturn($attribute);
    }

    public function it_finds_attributes_by_criteria(): void
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findBy(['code' => 'attribute_1'])->shouldReturn([$attribute]);
    }

    public function it_does_not_find_attributes_by_criteria(): void
    {
        $attribute = $this->createAttribute('attribute_1');
        $this->beConstructedWith([$attribute->getCode() => $attribute]);

        $this->findBy(['code' => 'attribute_2'])->shouldReturn([]);
    }

    public function it_finds_attributes_by_array_criteria(): void
    {
        $attribute1 = $this->createAttribute('attribute_1');
        $attribute2 = $this->createAttribute('attribute_2');
        $attribute3 = $this->createAttribute('attribute_3');

        $this->beConstructedWith([
            $attribute1->getCode() => $attribute1,
            $attribute2->getCode() => $attribute2,
            $attribute3->getCode() => $attribute3,
        ]);

        $this->findBy(['code' => ['attribute_1', 'attribute_2']])->shouldReturn([$attribute1, $attribute2]);
    }

    public function it_finds_one_attribute_by_array_criteria(): void
    {
        $attribute1 = $this->createAttribute('attribute_1');
        $attribute2 = $this->createAttribute('attribute_2');
        $attribute3 = $this->createAttribute('attribute_3');

        $this->beConstructedWith([
            $attribute1->getCode() => $attribute1,
            $attribute2->getCode() => $attribute2,
            $attribute3->getCode() => $attribute3,
        ]);

        $this->findOneBy(['code' => ['attribute_1', 'attribute_2']])->shouldReturn($attribute1);
    }

    public function it_throws_an_exception_if_saved_object_is_not_an_attribute(\StdClass $object): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The object argument should be a attribute'))
            ->during('save', [$object]);
    }

    public function it_gets_the_main_identifier_attribute(): void
    {
        $identifier = (new Attribute())
            ->setType(AttributeTypes::IDENTIFIER)
            ->setIsMainIdentifier(true)
        ;

        $this->save($identifier);
        $this->save((new Attribute())->setCode('name'));

        $this->getIdentifier()->shouldReturn($identifier);
    }

    public function it_gets_attribute_types_by_codes(): void
    {
        $attribute1 = $this->createAttribute('attribute_1', 'attributetype_1');
        $attribute2 = $this->createAttribute('attribute_2', 'attributetype_2');
        $attribute3 = $this->createAttribute('attribute_3', 'attributetype_3');

        $this->beConstructedWith(
            [
                $attribute1->getCode() => $attribute1,
                $attribute2->getCode() => $attribute2,
                $attribute3->getCode() => $attribute3,
            ]
        );

        $this->getAttributeTypeByCodes(['attribute_1', 'attribute_3'])->shouldReturn([
            'attribute_1' => 'attributetype_1',
            'attribute_3' => 'attributetype_3',
        ]);
    }

    public function it_finds_media_attribute_codes(): void
    {
        $attribute1 = $this->createAttribute('attribute_1', null, AttributeTypes::BACKEND_TYPE_BOOLEAN);
        $attribute2 = $this->createAttribute('attribute_2', null, AttributeTypes::BACKEND_TYPE_MEDIA);
        $attribute3 = $this->createAttribute('attribute_3', null, AttributeTypes::BACKEND_TYPE_INTEGER);
        $attribute4 = $this->createAttribute('attribute_4', null, AttributeTypes::BACKEND_TYPE_MEDIA);

        $this->beConstructedWith(
            [
                $attribute1->getCode() => $attribute1,
                $attribute2->getCode() => $attribute2,
                $attribute3->getCode() => $attribute3,
                $attribute4->getCode() => $attribute4,
            ]
        );

        $this->findMediaAttributeCodes()->shouldReturn(['attribute_2', 'attribute_4']);
    }

    public function it_finds_all_attributes(): void
    {
        $attribute1 = $this->createAttribute('attribute_1', null, AttributeTypes::BACKEND_TYPE_BOOLEAN);
        $attribute2 = $this->createAttribute('attribute_2', null, AttributeTypes::BACKEND_TYPE_MEDIA);
        $attribute3 = $this->createAttribute('attribute_3', null, AttributeTypes::BACKEND_TYPE_INTEGER);
        $attribute4 = $this->createAttribute('attribute_4', null, AttributeTypes::BACKEND_TYPE_MEDIA);

        $this->beConstructedWith([
            $attribute1->getCode() => $attribute1,
            $attribute2->getCode() => $attribute2,
            $attribute3->getCode() => $attribute3,
            $attribute4->getCode() => $attribute4,
        ]);

        $this->findAll()->shouldReturn([
            'attribute_1' => $attribute1,
            'attribute_2' => $attribute2,
            'attribute_3' => $attribute3,
            'attribute_4' => $attribute4,
        ]);
    }

    public function it_finds_the_attribute_codes_by_type(): void
    {
        $attribute1 = $this->createAttribute('attribute_1', AttributeTypes::BOOLEAN);
        $attribute2 = $this->createAttribute('attribute_2', AttributeTypes::FILE);
        $attribute3 = $this->createAttribute('attribute_3', AttributeTypes::NUMBER);
        $attribute4 = $this->createAttribute('attribute_4', AttributeTypes::FILE);
        $this->beConstructedWith([
            $attribute1->getCode() => $attribute1,
            $attribute2->getCode() => $attribute2,
            $attribute3->getCode() => $attribute3,
            $attribute4->getCode() => $attribute4,
        ]);

        $this->getAttributeCodesByType(AttributeTypes::FILE)->shouldReturn(['attribute_2', 'attribute_4']);
        $this->getAttributeCodesByType(AttributeTypes::BOOLEAN)->shouldReturn(['attribute_1']);
        $this->getAttributeCodesByType(AttributeTypes::DATE)->shouldReturn([]);
    }

    public function it_changes_the_main_identifier(): void
    {
        $firstIdentifier = (new Attribute())
            ->setType(AttributeTypes::IDENTIFIER)
            ->setCode('name')
            ->setIsMainIdentifier(true)
        ;

        $this->save($firstIdentifier);

        $this->getIdentifier()->shouldReturn($firstIdentifier);

        $secondIdentifier = (new Attribute())
            ->setType(AttributeTypes::IDENTIFIER)
            ->setCode('new_one')
        ;
        $this->save($secondIdentifier);
        $this->updateMainIdentifier($secondIdentifier);
        $this->getIdentifier()->shouldReturn($secondIdentifier);

        $this->getAttributeCodesByType(AttributeTypes::IDENTIFIER)->shouldReturn(['name', 'new_one']);
    }

    private function createAttribute(string $code, string $type = null, string $backendType = null): AttributeInterface
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        if (null !== $type) {
            $attribute->setType($type);
        }

        if (null !== $backendType) {
            $attribute->setBackendType($backendType);
        }

        return $attribute;
    }
}
