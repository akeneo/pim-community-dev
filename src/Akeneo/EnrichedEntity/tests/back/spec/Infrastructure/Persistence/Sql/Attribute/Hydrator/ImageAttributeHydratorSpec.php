<?php

namespace spec\Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\ImageAttributeHydrator;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ImageAttributeHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImageAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_image_attributes()
    {
        $this->supports(['attribute_type' => 'image'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $platform = new MySqlPlatform();
        $message = sprintf(
            'Impossible to hydrate the image attribute because some information is missing: %s',
            implode(', ', ImageAttributeHydrator::EXPECTED_KEYS)
        );
        $this->shouldThrow(new \RuntimeException($message))->during('hydrate', [$platform, ['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_an_image_attribute_with_no_max_file_size_and_no_extensions()
    {
        $platform = new MySqlPlatform();
        $textArea = $this->hydrate($platform, [
            'identifier'                 => 'picture_designer_fingerprint',
            'code'                       => 'picture',
            'enriched_entity_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Image']),
            'attribute_type'             => 'image',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'wrong_key'                  => '1',
            'additional_properties'      => json_encode([
                'max_file_size'      => null,
                'allowed_extensions' => [],
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(ImageAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'picture_designer_fingerprint',
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'picture',
            'labels'                     => ['fr_FR' => 'Image'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'image',
            'max_file_size'              => null,
            'allowed_extensions'         => [],
        ]);
    }

    function it_hydrates_an_image_attribute_with_a_max_file_size_and_with_allowed_extensions()
    {
        $platform = new MySqlPlatform();
        $textArea = $this->hydrate($platform, [
            'identifier'                 => 'picture_designer_fingerprint',
            'code'                       => 'picture',
            'enriched_entity_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Image']),
            'attribute_type'             => 'image',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'wrong_key'                  => '1',
            'additional_properties'      => json_encode([
                'max_file_size'      => '252.12',
                'allowed_extensions' => ['png', 'jpeg'],
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(ImageAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'picture_designer_fingerprint',
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'picture',
            'labels'                     => ['fr_FR' => 'Image'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'image',
            'max_file_size'              => '252.12',
            'allowed_extensions'         => ['png', 'jpeg'],
        ]);
    }
}
