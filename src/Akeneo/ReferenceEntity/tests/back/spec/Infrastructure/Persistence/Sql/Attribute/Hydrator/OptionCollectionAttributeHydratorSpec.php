<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\OptionCollectionAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class OptionCollectionAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionCollectionAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_option_collection_attributes()
    {
        $this->supports(['attribute_type' => 'option_collection'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['code' => 'mentor']]);
    }

    function it_hydrates_an_option_attribute_with_no_options()
    {
        $optionAttribute = $this->hydrate([
            'identifier' => 'colors_designer_fingerprint',
            'code' => 'colors',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Couleurs']),
            'attribute_type' => 'option',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'attribute_options' => [ ],
            ]),
        ]);
        $optionAttribute->shouldBeAnInstanceOf(OptionCollectionAttribute::class);
        $optionAttribute->normalize()->shouldBe([
            'identifier' => 'colors_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'colors',
            'labels' => ['fr_FR' => 'Couleurs'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'option_collection',
            'attribute_options' => [ ]
        ]);
    }

    function it_hydrates_an_option_attribute_with_option()
    {
        $optionAttribute = $this->hydrate([
            'identifier' => 'colors_designer_fingerprint',
            'code' => 'colors',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Couleurs']),
            'attribute_type' => 'option',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'attribute_options' => [
                    [
                        'code' => 'red',
                        'labels' => [
                            'fr_FR' => 'Rouge'
                        ]
                    ],
                    [
                        'code'   => 'green',
                        'labels' => [
                            'fr_FR' => 'Vert',
                        ],
                    ]
                ],
            ]),
        ]);
        $optionAttribute->shouldBeAnInstanceOf(OptionCollectionAttribute::class);
        $optionAttribute->normalize()->shouldBe([
            'identifier' => 'colors_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'colors',
            'labels' => ['fr_FR' => 'Couleurs'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'option_collection',
            'attribute_options' => [
                [
                    'code' => 'red',
                    'labels' => [
                        'fr_FR' => 'Rouge'
                    ]
                ],
                [
                    'code'   => 'green',
                    'labels' => [
                        'fr_FR' => 'Vert',
                    ],
                ]
            ]
        ]);
    }
}
