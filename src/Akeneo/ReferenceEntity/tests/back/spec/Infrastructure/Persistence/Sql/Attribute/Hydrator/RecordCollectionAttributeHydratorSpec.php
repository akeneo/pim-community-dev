<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\RecordCollectionAttributeHydrator;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;
use Doctrine\DBAL\Connection;

class RecordCollectionAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_record_collection_attributes()
    {
        $this->supports(['attribute_type' => 'record_collection'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['code' => 'brands']]);
    }

    function it_hydrates_a_record_attribute()
    {
        $recordCollectionAttribute = $this->hydrate([
            'identifier' => 'brands_designer_fingerprint',
            'code' => 'brands',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Marques']),
            'attribute_type' => 'record',
            'attribute_order' => '0',
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'additional_properties' => json_encode([
                'record_type' => 'designer',
            ]),
        ]);
        $recordCollectionAttribute->shouldBeAnInstanceOf(RecordCollectionAttribute::class);
        $recordCollectionAttribute->normalize()->shouldBe([
            'identifier' => 'brands_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'order' => 0,
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'record_collection',
            'record_type' => 'designer',
        ]);
    }
}
