<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter;

use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;
use Doctrine\DBAL\Connection;

class UserCatalogChannelResetter implements UserConfigurationResetterInterface
{
    private const DEFAULT_CATALOG_CHANNEL_CODE = 'ecommerce';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): void
    {
        $this->connection->executeStatement(<<<SQL
            UPDATE oro_user SET catalogScope_id = (
                SELECT id
                FROM pim_catalog_channel 
                WHERE code = :defaultCatalogChannelCode
            ) 
            WHERE catalogScope_id NOT IN (
                SELECT id 
                FROM pim_catalog_channel
            )
        SQL, [
            'defaultCatalogChannelCode' => self::DEFAULT_CATALOG_CHANNEL_CODE,
        ]);
    }
}
