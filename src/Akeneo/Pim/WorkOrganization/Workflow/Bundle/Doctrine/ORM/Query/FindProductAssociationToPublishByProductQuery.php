<?php


namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindProductAssociationToPublishByProductQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindProductAssociationToPublishByProductQuery implements FindProductAssociationToPublishByProductQueryInterface
{
    private Connection $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ProductInterface $product): array
    {
        $sql = <<<SQL
SELECT pp.id as product_id , a.association_type_id
FROM pim_catalog_association_product ap
    JOIN pim_catalog_association a ON ap.association_id = a.id
    JOIN pimee_workflow_published_product pp ON a.owner_uuid = pp.original_product_uuid
WHERE ap.product_uuid = :product_uuid
SQL;
        return $this->connection->executeQuery($sql, ['product_uuid' => $product->getUuid()->getBytes()])
            ->fetchAllAssociative();
    }
}
