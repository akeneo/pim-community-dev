<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use PhpSpec\ObjectBehavior;

class AssetDetailsSpec extends ObjectBehavior
{
    public function let(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeIdentifier $attributeAsMainMediaIdentifier,
        AssetCode $code,
        LabelCollection $labelCollection
    ) {
        $this->beConstructedWith(
            $identifier,
            $assetFamilyIdentifier,
            $attributeAsMainMediaIdentifier,
            $code,
            $labelCollection,
            ['image_value'],
            [],
            true
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetDetails::class);
    }

    public function it_normalizes_itself(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeIdentifier $attributeAsMainMediaIdentifier,
        AssetCode $code,
        LabelCollection $labelCollection
    ) {
        $identifier->normalize()->willReturn('starck_designer_fingerprint');
        $assetFamilyIdentifier->normalize()->willReturn('designer');
        $attributeAsMainMediaIdentifier->normalize()->willReturn('main_image');
        $code->normalize()->willReturn('starck');
        $labelCollection->normalize()->willReturn(['fr_FR' => 'Philippe Starck']);

        $this->normalize()->shouldReturn([
            'identifier'                         => 'starck_designer_fingerprint',
            'asset_family_identifier'            => 'designer',
            'attribute_as_main_media_identifier' => 'main_image',
            'code'                               => 'starck',
            'labels'                             => ['fr_FR' => 'Philippe Starck'],
            'image'                              => ['image_value'],
            'values'                             => [],
            'permission'                         => [
                'edit' => true,
            ],
        ]);
    }
}
