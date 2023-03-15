<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\AssetManager;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type AssetAttribute array{
 *      identifier: string,
 *      labels: array<string, string>,
 *      type: string,
 *      scopable: bool,
 *      localizable: bool
 * }
 */
interface FindOneAssetAttributeByIdentifierQueryInterface
{
    /**
     * @return AssetAttribute|null
     */
    public function execute(string $identifier, string $locale = 'en_US'): ?array;
}
