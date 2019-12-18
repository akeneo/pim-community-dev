<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use PhpSpec\ObjectBehavior;

class MediaLinkValueHydratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldImplement(ValueHydratorInterface::class);
    }

    public function it_supports_media_link_value_attributes(
        MediaLinkAttribute $UrlAttribute,
        AbstractAttribute $otherAttribute
    ) {
        $this->supports($UrlAttribute)->shouldReturn(true);
        $this->supports($otherAttribute)->shouldReturn(false);
    }

    public function it_generates_the_url_to_get_the_preview_of_the_url_attribute(
        MediaLinkAttribute $urlAttribute,
        AttributeIdentifier $attributeIdentifier,
        Prefix $prefix,
        Suffix $suffix
    ) {
        $urlAttribute->getIdentifier()->willReturn($attributeIdentifier);
        $urlAttribute->getPrefix()->willReturn($prefix);
        $prefix->normalize()->willReturn('http://nice.com/');
        $urlAttribute->getSuffix()->willReturn($suffix);
        $suffix->normalize()->willReturn('.png');
        $attributeIdentifier->stringValue()->willReturn('front_picture_finrgerprint');

        $this->hydrate(
            [
                'attribute' => 'front_picture_finrgerprint',
                'locale'    => null,
                'channel'   => null,
                'data'      => 'house',
            ],
            $urlAttribute
        )->shouldReturn(
            [
                'attribute' => 'front_picture_finrgerprint',
                'locale'    => null,
                'channel'   => null,
                'data'      => 'house',
            ]
        );
    }
}
