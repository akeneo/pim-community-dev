<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('asset', 'image', 'test'),
                AssetFamilyIdentifier::fromString('asset'),
                AttributeCode::fromString('image'),
                LabelCollection::fromArray(['fr_FR' => 'Image', 'en_US' => 'Image']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                Prefix::fromString('http:://www.binder.com'),
                Suffix::fromString('/500x500'),
                MediaType::fromString('image'),
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkAttribute::class);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_creates_an_image_media_link()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                Prefix::fromString('http:://www.binder.com'),
                Suffix::fromString('/500'),
                MediaType::fromString('image'),
            ]
        );
    }

    function it_creates_an_image_media_link_without_prefix_and_suffix()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                Prefix::createEmpty(),
                Suffix::createEmpty(),
                MediaType::fromString('image'),
            ]
        );
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'identifier'                  => 'image_asset_test',
                'asset_family_identifier' => 'asset',
                'code'                        => 'image',
                'labels'                      => ['fr_FR' => 'Image', 'en_US' => 'Image'],
                'order'                       => 0,
                'is_required'                 => true,
                'is_read_only'                => false,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'media_link',
                'media_type'                => 'image',
                'prefix'                      => 'http:://www.binder.com',
                'suffix'                      => '/500x500'
            ]
        );
    }
}
