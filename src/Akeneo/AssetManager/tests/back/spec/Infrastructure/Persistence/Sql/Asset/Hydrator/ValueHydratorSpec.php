<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\DataHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\DataHydratorRegistry;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ValueHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ValueHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $sqlConnection,
        DataHydratorRegistry $dataHydratorRegistry
    ) {
        $sqlConnection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($sqlConnection, $dataHydratorRegistry);
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
