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

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;

interface AttributeRepositoryInterface
{
    public function create(AbstractAttribute $attribute): void;

    public function update(AbstractAttribute $attribute): void;

    /**
     * @throws AttributeNotFoundException
     */
    public function deleteByIdentifier(AttributeIdentifier $attributeIdentifier): void;

    /**
     * @throws AttributeNotFoundException
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute;

    /**
     * @throws AttributeNotFoundException
     */
    public function getByCodeAndAssetFamilyIdentifier(AttributeCode $code, AssetFamilyIdentifier $assetFamilyIdentifier): AbstractAttribute;

    /**
     * @return AbstractAttribute[]
     */
    public function findByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): array;

    /**
     * Count attributes for a given asset family
     */
    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int;

    public function nextIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): AttributeIdentifier;
}
