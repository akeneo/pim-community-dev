<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

class GetTransformationsSource
{
    private GetTransformations $getTransformations;

    public function __construct(GetTransformations $getTransformations)
    {
        $this->getTransformations = $getTransformations;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param LocaleReference $localeReference
     * @param ChannelReference $channelReference
     * @return Transformation[]
     */
    public function forAttribute(
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): array {
        $transformationSourcedByAttribute = [];

        $transformations = $this->getTransformationsByAssetFamilyIdentifier($attribute->getAssetFamilyIdentifier());
        foreach ($transformations as $transformation) {
            if ($this->transformationIsSourcedByAttribute($transformation, $attribute, $channelReference, $localeReference)) {
                $transformationSourcedByAttribute[] = $transformation;
            }
        }

        return $transformationSourcedByAttribute;
    }

    private function getTransformationsByAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier)
    {
        try {
            return $this->getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $exception) {
            return [];
        }
    }

    private function transformationIsSourcedByAttribute(
        Transformation $transformation,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): bool {
        $sourceTransformation = $transformation->getSource();

        return $sourceTransformation->getAttributeCode()->equals($attribute->getCode()) &&
            $sourceTransformation->getLocaleReference()->equals($localeReference) &&
            $sourceTransformation->getChannelReference()->equals($channelReference);
    }
}
