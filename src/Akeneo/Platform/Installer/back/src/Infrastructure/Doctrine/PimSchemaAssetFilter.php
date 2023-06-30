<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Doctrine;

use Doctrine\DBAL\Schema\AbstractAsset;

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimSchemaAssetFilter
{
    private const BLACKLIST = [
        'pim_configuration',
    ];

    public function __invoke(string|AbstractAsset $assetName): bool
    {
        if ($assetName instanceof AbstractAsset) {
            $assetName = $assetName->getName();
        }

        return !in_array($assetName, self::BLACKLIST, true);
    }
}
