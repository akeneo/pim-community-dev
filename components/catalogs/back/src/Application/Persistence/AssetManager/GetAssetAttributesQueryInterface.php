<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\AssetManager;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AssetAttribute from FindOneAssetAttributeByIdentifierQueryInterface
 */
interface GetAssetAttributesQueryInterface
{
    /**
     * @param array<string> $types
     *
     * @return array<AssetAttribute>
     */
    public function execute(
        string $assetFamilyIdentifier,
        array $types = [],
        string $locale = 'en_US',
    ): array;
}
