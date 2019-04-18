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

namespace PimEnterprise\Bundle\ProductAssetBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use PimEnterprise\Component\ProductAsset\Persistence\Query\FindAssetCodesWithMissingVariationWithFileInterface;

class FindAssetCodesWithMissingVariationWithFile implements FindAssetCodesWithMissingVariationWithFileInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): array
    {
        $sql = <<<SQL
    SELECT asset.code
    FROM pimee_product_asset_reference AS reference
      INNER JOIN pimee_product_asset_asset AS asset ON reference.asset_id = asset.id
      INNER JOIN pim_catalog_channel_locale AS channel_locale ON channel_locale.locale_id = reference.locale_id
      LEFT JOIN pimee_product_asset_variation AS variation 
        ON reference.id = variation.reference_id AND variation.channel_id = channel_locale.channel_id
    WHERE reference.locale_id IS NOT NULL 
      AND reference.file_info_id IS NOT NULL 
      AND variation.id IS NULL
UNION
    SELECT asset.code
    FROM pimee_product_asset_reference AS reference
      CROSS JOIN pim_catalog_channel AS channel
      INNER JOIN pimee_product_asset_asset AS asset ON reference.asset_id = asset.id
      LEFT JOIN pimee_product_asset_variation AS variation 
        ON reference.id = variation.reference_id AND variation.channel_id = channel.id
    WHERE reference.locale_id IS NULL  
      AND reference.file_info_id IS NOT NULL 
      AND variation.id IS NULL
SQL;
        $statement = $this->connection->query($sql);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
