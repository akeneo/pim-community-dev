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

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

class CheckIfTransformationTarget
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function forAttribute(
        AbstractAttribute $attribute,
        ?string $locale,
        ?string $channel
    ): bool {
        $commandLocaleReference = $locale !== null ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($locale)) :
            LocaleReference::noReference();
        $commandChannelReference = $channel !== null ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($channel)) :
            ChannelReference::noReference();

        $transformations = $this->assetFamilyRepository
            ->getByIdentifier($attribute->getAssetFamilyIdentifier())
            ->getTransformationCollection();

        foreach ($transformations as $transformation) {
            /** @var $transformation Transformation */
            $target = $transformation->getTarget();

            if ($target->getAttributeCode()->equals($attribute->getCode()) &&
                $target->getLocaleReference()->equals($commandLocaleReference) &&
                $target->getChannelReference()->equals($commandChannelReference)
            ) {
                return true;
            }
        }

        return false;
    }
}
