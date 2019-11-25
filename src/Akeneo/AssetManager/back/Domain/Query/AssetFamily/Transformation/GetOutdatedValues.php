<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class GetOutdatedValues
{
    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    public function __construct(GetAttributeIdentifierInterface $getAttributeIdentifier)
    {
        $this->getAttributeIdentifier = $getAttributeIdentifier;
    }

    /**
     * @param Asset $asset
     * @param TransformationCollection $transformationCollection
     * @return ValueCollection
     */
    public function fromAsset(
        Asset $asset,
        TransformationCollection $transformationCollection
    ): array {
        $result = [];
        foreach ($transformationCollection as $transformation) {
            /** @var Transformation $transformation */
            $sourceValue = $this->getValue($asset, $transformation->getSource());
            $targetValue = $this->getValue($asset, $transformation->getTarget());

            if ($sourceValue === null) {
                continue;
            }

            if ($targetValue === null) {
                $result[] = $this->createEmptyValue($asset, $transformation);

                continue;
            }

            if (!($sourceValue->getData() instanceof FileData) || !($targetValue->getData() instanceof FileData)) {
                continue;
            }

            if (null === $sourceValue->getData()->getUpdatedAt() ||
                null === $targetValue->getData()->getUpdatedAt() ||
                $sourceValue->getData()->getUpdatedAt() > $targetValue->getData()->getUpdatedAt()
            ) {
                $result[] = $targetValue;
            }
        }

        return $result;
    }

    private function getValue(Asset $asset, TransformationReference $reference): ?Value
    {
        $refAttributeCode = $reference->getAttributeCode();
        $assetFamilyIdentifier = $asset->getAssetFamilyIdentifier();
        $refAttributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            $refAttributeCode
        );

        $refKey = ValueKey::create(
            AttributeIdentifier::create(
                $assetFamilyIdentifier->__toString(),
                $refAttributeCode->__toString(),
                $refAttributeIdentifier->stringValue()
            ),
            $reference->getChannelReference(),
            $reference->getLocaleReference()
        );

        return $asset->findValue($refKey);
    }

    private function createEmptyValue(
        Asset $asset,
        Transformation $transformation
    ): Value {
        return Value::create(
            $this->getAttributeIdentifier->withAssetFamilyAndCode(
                $asset->getAssetFamilyIdentifier(),
                $transformation->getTarget()->getAttributeCode()
            ),
            $transformation->getTarget()->getChannelReference(),
            $transformation->getTarget()->getLocaleReference(),
            EmptyData::create()
        );
    }
}
