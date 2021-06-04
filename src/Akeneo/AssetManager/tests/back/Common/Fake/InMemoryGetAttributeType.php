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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGetAttributeType implements GetAttributeTypeInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): string
    {
        $attributes = $this->attributeRepository->getAttributes();

        foreach ($attributes as $attribute) {
            $sameAssetFamily = $attribute->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier);
            $sameCode = $attribute->getCode()->equals($attributeCode);

            if ($sameAssetFamily && $sameCode) {
                return $attribute->getType();
            }
        }

        throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $attributeCode);
    }
}
