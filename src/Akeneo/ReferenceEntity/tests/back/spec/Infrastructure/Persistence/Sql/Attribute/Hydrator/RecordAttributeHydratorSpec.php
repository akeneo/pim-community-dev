<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\RecordAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class RecordAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_record_attributes()
    {
        $this->supports(['attribute_type' => 'record'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['code' => 'mentor']]);
    }

    function it_hydrates_a_record_attribute()
    {
        $recordAttribute = $this->hydrate([
            'identifier' => 'mentor_designer_fingerprint',
            'code' => 'mentor',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Mentor']),
            'attribute_type' => 'record',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'record_type' => 'designer',
            ]),
        ]);
        $recordAttribute->shouldBeAnInstanceOf(RecordAttribute::class);
        $recordAttribute->normalize()->shouldBe([
            'identifier' => 'mentor_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'record',
            'record_type' => 'designer',
        ]);
    }
}
