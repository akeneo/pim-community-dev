<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsExecutorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeTransformationsExecutor::class);
    }

    function it_only_accepts_asset_identifiers()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('execute', [[new \stdClass()]]);
    }

    // TODO
    function it_does_nothing()
    {
        $this
            ->shouldThrow(new \Exception('TODO; to implement'))
            ->during('execute', [[AssetIdentifier::fromString('assetIdentifier1')]]);
    }
}
