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

namespace PimEnterprise\Bundle\ProductAssetBundle\Persistence;

use Doctrine\DBAL\Connection;
use PimEnterprise\Component\ProductAsset\Persistence\DeleteVariationsForChannelId;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DeleteVariationsForChannelIdFromDB implements DeleteVariationsForChannelId
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $channelId): void
    {
        $sql = <<<SQL
DELETE FROM pimee_product_asset_variation
WHERE channel_id = :channelId;
SQL;

        $this->connection->executeUpdate(
            $sql,
            ['channelId' => $channelId],
            ['channelId' => \PDO:: PARAM_INT]
        );
    }
}
