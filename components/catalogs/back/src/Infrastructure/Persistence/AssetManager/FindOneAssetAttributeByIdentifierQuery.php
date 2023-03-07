<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\AssetManager;

use Akeneo\Catalogs\Application\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneAssetAttributeByIdentifierQuery implements FindOneAssetAttributeByIdentifierQueryInterface
{
    public function execute(string $identifier): ?array
    {
        return null;
    }
}
