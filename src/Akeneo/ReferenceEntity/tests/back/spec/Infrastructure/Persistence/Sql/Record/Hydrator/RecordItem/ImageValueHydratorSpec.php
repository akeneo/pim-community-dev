<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ImagePreviewUrlGenerator;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorInterface;
use PhpSpec\ObjectBehavior;

class ImageValueHydratorSpec extends ObjectBehavior
{
    public function let(ImagePreviewUrlGenerator $imagePreviewUrlGenerator)
    {
        $this->beConstructedWith($imagePreviewUrlGenerator);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(ValueHydratorInterface::class);
    }

    public function it_supports_record_type_attributes(
        ImageAttribute $imageAttribute,
        AbstractAttribute $otherAttribute
    ) {
        $this->supports($imageAttribute)->shouldReturn(true);
        $this->supports($otherAttribute)->shouldReturn(false);
    }

    public function it_generates_the_url_to_get_the_preview_of_the_image_attribute(
        ImageAttribute $imageAttribute,
        AttributeIdentifier $attributeIdentifier,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $previewUrl = 'https://my-pim.com/images/house.png/500x500';

        $imageAttribute->getIdentifier()->willReturn($attributeIdentifier);
        $attributeIdentifier->stringValue()->willReturn('front_picture_finrgerprint');

        $imagePreviewUrlGenerator
            ->generate('house.png', 'front_picture_finrgerprint', 'thumbnail')
            ->willReturn($previewUrl);

        $this->hydrate(
            [
                'attribute' => 'front_picture_finrgerprint',
                'locale'    => null,
                'channel'   => null,
                'data'      => ['filePath' => 'house.png'],
            ],
            $imageAttribute
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
