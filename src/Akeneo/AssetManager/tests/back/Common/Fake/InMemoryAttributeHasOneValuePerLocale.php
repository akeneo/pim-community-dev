<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeHasOneValuePerLocaleInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAttributeHasOneValuePerLocale implements AttributeHasOneValuePerLocaleInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): bool
    {
        $attributes = $this->attributeRepository->getAttributes();
        foreach ($attributes as $attribute) {
            $sameAssetFamily = $attribute->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier);
            $sameCode = $attribute->getCode()->equals($attributeCode);

            if ($sameAssetFamily && $sameCode) {
                return $attribute->hasValuePerLocale();
            }
        }

        throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $attributeCode);
    }
}
