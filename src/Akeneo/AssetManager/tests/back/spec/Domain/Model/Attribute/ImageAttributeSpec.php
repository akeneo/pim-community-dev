<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class ImageAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Portrait', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('300'),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImageAttribute::class);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
                'identifier'                 => 'image_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'is_required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_label_and_returns_a_new_instance_of_itself()
    {
        $this->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Portrait', 'de_DE' => 'Porträt']));
        $this->normalize()->shouldBe([
                'identifier'                 => 'image_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait', 'de_DE' => 'Porträt'],
                'order'                      => 0,
                'is_required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_allowed_extensions_and_returns_a_new_instance_of_itself()
    {
        $this->setAllowedExtensions(AttributeAllowedExtensions::fromList(['jpeg']));
        $this->normalize()->shouldBe([
                'identifier'                 => 'image_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'is_required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['jpeg'],
            ]
        );
    }

    function it_updates_its_max_file_size_and_returns_a_new_instance_of_itself()
    {
        $this->setMaxFileSize(AttributeMaxFileSize::fromString('1000'));
        $this->normalize()->shouldBe([
                'identifier'                 => 'image_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'is_required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '1000',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_is_required_property_size_and_returns_a_new_instance_of_itself()
    {
        $this->setIsRequired(AttributeIsRequired::fromBoolean(false));
        $this->normalize()->shouldBe([
                'identifier'                 => 'image_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'is_required'                   => false,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_tells_if_it_has_a_value_per_channel()
    {
        $this->hasValuePerChannel()->shouldReturn(true);
    }

    function it_tells_if_it_has_a_value_per_locale()
    {
        $this->hasValuePerLocale()->shouldReturn(true);
    }
}
