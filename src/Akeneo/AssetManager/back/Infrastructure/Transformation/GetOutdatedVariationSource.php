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

namespace Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\AssetFamily\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\AssetManager\Application\AssetFamily\Transformation\GetOutdatedVariationSourceInterface;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * This class checks that a transformation is applicable to a given asset, meaning:
 * - the values corresponding to the transformation's source and target are of the right type (MediaFile)
 * - the target value is older than the source value
 */
class GetOutdatedVariationSource implements GetOutdatedVariationSourceInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Asset $asset
     * @param Transformation $transformation
     *
     * @return FileData|null
     *
     * @throws NonApplicableTransformationException
     */
    public function forAssetAndTransformation(Asset $asset, Transformation $transformation): ?FileData
    {
        $source = $transformation->getSource();
        $sourceAttribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            $source->getAttributeCode(),
            $asset->getAssetFamilyIdentifier()
        );
        if (!($sourceAttribute instanceof MediaFileAttribute)) {
            $message = sprintf(
                'Source attribute "%s" is not a media file attribute',
                $sourceAttribute->getCode()->__toString()
            );
            throw new NonApplicableTransformationException($message);
        }

        $sourceValue = $asset->findValue(
            ValueKey::create(
                $sourceAttribute->getIdentifier(),
                $source->getChannelReference(),
                $source->getLocaleReference()
            )
        );
        if (null === $sourceValue) {
            $message = sprintf(
                'The source file for attribute "%s" is missing',
                $sourceAttribute->getCode()->__toString()
            );
            throw new NonApplicableTransformationException($message);
        }

        $target = $transformation->getTarget();
        $targetAttribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            $target->getAttributeCode(),
            $asset->getAssetFamilyIdentifier()
        );

        if (!($targetAttribute instanceof MediaFileAttribute)) {
            $message = sprintf(
                'Target attribute "%s" is not a media file attribute',
                $targetAttribute->getCode()->__toString()
            );
            throw new NonApplicableTransformationException($message);
        }
        $targetValue = $asset->findValue(
            ValueKey::create(
                $targetAttribute->getIdentifier(),
                $target->getChannelReference(),
                $target->getLocaleReference()
            )
        );

        if (null === $targetValue
            || $this->isTargetValueOlderThanSource($sourceValue->getData(), $targetValue->getData())
            || $this->isTargetValueOlderThanTransformationSetup($transformation, $targetValue->getData())
        ) {
            return $sourceValue->getData();
        }

        return null;
    }

    private function isTargetValueOlderThanSource(FileData $sourceData, FileData $targetData): bool
    {
        return null === $targetData->getUpdatedAt() || $targetData->getUpdatedAt() < $sourceData->getUpdatedAt();
    }

    private function isTargetValueOlderThanTransformationSetup(
        Transformation $transformation,
        FileData $targetData
    ): bool {
        return null === $targetData->getUpdatedAt() || $targetData->getUpdatedAt() < $transformation->getUpdatedAt();
    }
}
