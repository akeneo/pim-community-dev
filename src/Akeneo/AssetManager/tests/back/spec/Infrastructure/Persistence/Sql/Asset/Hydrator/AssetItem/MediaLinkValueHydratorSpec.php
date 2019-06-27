<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use PhpSpec\ObjectBehavior;

class MediaLinkValueHydratorSpec extends ObjectBehavior
{
    public function let(ImagePreviewUrlGenerator $imagePreviewUrlGenerator)
    {
        $this->beConstructedWith($imagePreviewUrlGenerator);
    }

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
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $previewUrl = 'https://my-pim.com/images/house.png/500x500';

        $urlAttribute->getIdentifier()->willReturn($attributeIdentifier);
        $attributeIdentifier->stringValue()->willReturn('front_picture_finrgerprint');

        $imagePreviewUrlGenerator
            ->generate('house.png', 'front_picture_finrgerprint', 'thumbnail')
            ->willReturn($previewUrl);

        $this->hydrate(
            [
                'attribute' => 'front_picture_finrgerprint',
                'locale'    => null,
                'channel'   => null,
                'data'      => 'house.png',
            ],
            $urlAttribute
        )->shouldReturn(
            [
                'attribute' => 'front_picture_finrgerprint',
                'locale'    => null,
                'channel'   => null,
                'data'      => $previewUrl,
            ]
        );
    }
}
