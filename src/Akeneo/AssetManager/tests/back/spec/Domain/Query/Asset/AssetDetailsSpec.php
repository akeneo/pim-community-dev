<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

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
        AssetCode $code,
        LabelCollection $labelCollection
    ) {
        $this->beConstructedWith(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            Image::createEmpty(),
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
        AssetCode $code,
        LabelCollection $labelCollection
    ) {

        $identifier->normalize()->willReturn('starck_designer_fingerprint');
        $assetFamilyIdentifier->normalize()->willReturn('designer');
        $code->normalize()->willReturn('starck');
        $labelCollection->normalize()->willReturn(['fr_FR' => 'Philippe Starck']);

        $this->normalize()->shouldReturn([
            'identifier'                  => 'starck_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                        => 'starck',
            'labels'                      => ['fr_FR' => 'Philippe Starck'],
            'image'                       => null,
            'values'                      => [],
            'permission'                  => [
                'edit' => true,
            ],
        ]);
    }
}
