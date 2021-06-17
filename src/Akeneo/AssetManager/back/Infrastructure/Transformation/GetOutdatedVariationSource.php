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
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationReference;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This class checks that a transformation is applicable to a given asset, meaning:
 * - the transformation is valid
 * - the target value is older than the source value
 */
class GetOutdatedVariationSource implements GetOutdatedVariationSourceInterface
{
    private AttributeRepositoryInterface $attributeRepository;

    private ValidatorInterface $validator;

    public function __construct(AttributeRepositoryInterface $attributeRepository, ValidatorInterface $validator)
    {
        $this->attributeRepository = $attributeRepository;
        $this->validator = $validator;
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
        $violations = $this->validator->validate(
            $transformation->normalize(),
            new Assert\Transformation($asset->getAssetFamilyIdentifier())
        );
        if ($violations->count() > 0) {
            throw new NonApplicableTransformationException($violations->get(0)->getMessage());
        }

        $sourceValue = $this->getValueForReference($transformation->getSource(), $asset);
        if (null === $sourceValue) {
            $message = sprintf(
                'The source file for attribute "%s" is missing',
                $transformation->getSource()->getAttributeCode()
            );
            throw new NonApplicableTransformationException($message);
        }

        $targetValue = $this->getValueForReference($transformation->getTarget(), $asset);
        if (null === $targetValue
            || $targetValue->getData()->getUpdatedAt() < $sourceValue->getData()->getUpdatedAt()
            || $targetValue->getData()->getUpdatedAt() < $transformation->getUpdatedAt()
        ) {
            return $sourceValue->getData();
        }

        return null;
    }

    private function getValueForReference(TransformationReference $reference, Asset $asset): ?Value
    {
        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            $reference->getAttributeCode(),
            $asset->getAssetFamilyIdentifier()
        );

        return $asset->findValue(
            ValueKey::create(
                $attribute->getIdentifier(),
                $reference->getChannelReference(),
                $reference->getLocaleReference()
            )
        );
    }
}
