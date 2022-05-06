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

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;

/**
 * Get the identifier of an existing attribute from its code and the asset family identifier
 */
interface GetAttributeIdentifierInterface
{
    /**
     * @throws \LogicException if the attribute identifier is not found
     */
    public function withAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier;
}
