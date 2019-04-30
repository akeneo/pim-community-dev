<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\NumberAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class NumberAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NumberAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_image_attributes()
    {
        $this->supports(['attribute_type' => 'number'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_a_number_attribute()
    {
        $number = $this->hydrate([
            'identifier'                 => 'area_city_fingerprint',
            'code'                       => 'area',
            'reference_entity_identifier' => 'city',
            'labels'                     => json_encode(['fr_FR' => 'Superficie']),
            'attribute_type'             => 'number',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'additional_properties'      => json_encode([]),
        ]);
        $number->shouldBeAnInstanceOf(NumberAttribute::class);
        $number->normalize()->shouldBe([
            'identifier'                 => 'area_city_fingerprint',
            'reference_entity_identifier' => 'city',
            'code'                       => 'area',
            'labels'                     => ['fr_FR' => 'Superficie'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'number',
        ]);
    }
}
