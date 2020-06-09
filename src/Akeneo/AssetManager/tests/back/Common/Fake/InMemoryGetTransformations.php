<?php

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;

class InMemoryGetTransformations implements GetTransformations
{
    public function fromAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): TransformationCollection
    {
        return TransformationCollection::noTransformation();
    }
}
