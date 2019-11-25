<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\FindTransformationsForAsset;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsExecutorSpec extends ObjectBehavior
{
    public function let(FindTransformationsForAsset $findTransformationsForAsset)
    {
        $this->beConstructedWith($findTransformationsForAsset);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeTransformationsExecutor::class);
    }

    function it_only_accepts_asset_identifiers()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('execute', [[new \stdClass()]]);
    }

    // TODO
    function it_does_nothing(FindTransformationsForAsset $findTransformationsForAsset)
    {
        $assetIdentifiers = [AssetIdentifier::fromString('identifier1')];

        $findTransformationsForAsset->fromAssetIdentifiers($assetIdentifiers)->willReturn([
            'identifier1' => TransformationCollection::noTransformation(),
        ]);

        $this
            ->shouldThrow(new \Exception('TODO; to implement'))
            ->during('execute', [$assetIdentifiers]);
    }
}
