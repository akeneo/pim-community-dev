<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\DataHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\DataHydratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ValueHydrator;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ValueHydratorSpec extends ObjectBehavior
{
    function let(
        DataHydratorRegistry $dataHydratorRegistry
    ) {
        $this->beConstructedWith($dataHydratorRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueHydrator::class);
    }

    function it_hydrates_a_localized_scopable_value(
        $dataHydratorRegistry,
        AbstractAttribute $attribute,
        DataHydratorInterface $dataHydrator
    ) {
        $dataHydratorRegistry->getHydrator($attribute)->willReturn($dataHydrator);
        $dataHydrator->hydrate('Text description', $attribute)
            ->willReturn(TextData::fromString('Text description'));
        $attribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('name_designer-fingerprint'));

        $this->hydrate(
            [
                'channel' => 'mobile',
                'locale'  => 'fr_FR',
                'data'    => 'Text description',
            ],
            $attribute
        )->normalize()->shouldReturn([
            'attribute' => 'name_designer-fingerprint',
            'channel'   => 'mobile',
            'locale'    => 'fr_FR',
            'data'      => 'Text description',
        ]);
    }

    function it_hydrates_a_non_scopable_non_localizable_value(
        $dataHydratorRegistry,
        AbstractAttribute $attribute,
        DataHydratorInterface $dataHydrator
    ) {
        $dataHydratorRegistry->getHydrator($attribute)->willReturn($dataHydrator);
        $dataHydrator->hydrate('Text description', $attribute)
            ->willReturn(TextData::fromString('Text description'));
        $attribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('name_designer-fingerprint'));

        $this->hydrate(
            [
                'channel' => null,
                'locale'  => null,
                'data'    => 'Text description',
            ],
            $attribute
        )->normalize()->shouldReturn([
            'attribute' => 'name_designer-fingerprint',
            'channel'   => null,
            'locale'    => null,
            'data'      => 'Text description',
        ]);
    }

    function it_throws_if_the_row_is_invalid(AbstractAttribute $attribute)
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('hydrate', [['malformed_value'], $attribute]);
    }
}
