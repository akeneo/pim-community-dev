<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductModelCodesQuery implements GetIdMappingFromProductModelCodesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productModelCodes): IdMapping
    {
        if (empty($productModelCodes)) {
            return IdMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT id, code from pim_catalog_product_model WHERE code IN (:product_codes)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_codes' => $productModelCodes],
            ['product_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative(), 'code', 'id');

        return IdMapping::createFromMapping($mapping);
    }
}
