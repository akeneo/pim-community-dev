<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\OptionAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class OptionAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_option_attributes()
    {
        $this->supports(['attribute_type' => 'option'])->shouldReturn(true);
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
            'identifier' => 'favorite_color_designer_fingerprint',
            'code' => 'favorite_color',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Couleur favorite']),
            'attribute_type' => 'option',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'options' => [],
            ]),
        ]);
        $optionAttribute->shouldBeAnInstanceOf(OptionAttribute::class);
        $optionAttribute->normalize()->shouldBe([
            'identifier' => 'favorite_color_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'favorite_color',
            'labels' => ['fr_FR' => 'Couleur favorite'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'option',
            'options' => []
        ]);
    }

    function it_hydrates_an_option_attribute_with_options()
    {
        $optionAttribute = $this->hydrate([
            'identifier' => 'favorite_color_designer_fingerprint',
            'code' => 'favorite_color',
            'reference_entity_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Couleur favorite']),
            'attribute_type' => 'option',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'options' => [
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
                    ],
                ],
            ]),
        ]);
        $optionAttribute->shouldBeAnInstanceOf(OptionAttribute::class);
        $optionAttribute->normalize()->shouldBe([
            'identifier' => 'favorite_color_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'favorite_color',
            'labels' => ['fr_FR' => 'Couleur favorite'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'option',
            'options' => [
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
                ],
            ]
        ]);
    }
}
