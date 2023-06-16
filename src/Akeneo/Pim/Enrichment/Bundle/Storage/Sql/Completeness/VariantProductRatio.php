<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Doctrine\DBAL\Connection;

/**
 * Query variant product completenesses to build the complete variant product ratio on the PMEF
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class VariantProductRatio implements VariantProductRatioInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return CompleteVariantProducts
     */
    public function findComplete(
        ProductModelInterface $productModel,
    ): CompleteVariantProducts {
        $join = null;

        if (2 === $productModel->getFamilyVariant()->getNumberOfLevel() && $productModel->isRootProductModel()) {
            $join = $this->joinToProductWithTwoLevels();
        } else {
            $join = $this->joinToProductWithOneLevel();
        }

        return $this->fetchResults($join, $productModel);
    }

    /**
     * At first, the `product_model_id` filtering on to check that is not null seems to be irrelevant.
     * Actually, it is useful because Mysql has not enough information to determine itself that `product_model_id` is not null.
     * Therefore, if there are many empty values for `product_model_id` in the database (= many simple products),
     * it does not use the index on parent id when looking for a variant product.
     * In such case, Mysql think that filtering on `product_model_id` is not a good filter and prefer to perform a very expensive Full Table Scan on the product table.
     * See https://dev.mysql.com/doc/refman/8.0/en/index-statistics.html
     * Another option would be to use `innodb_stats_method=nulls_unequal` but it can have many side effects.
     */
    private function joinToProductWithTwoLevels(): string
    {
        return <<<SQL
                FROM pim_catalog_product_model AS root_product_model
                INNER JOIN pim_catalog_product_model as sub_product_model ON sub_product_model.parent_id = root_product_model.id
                INNER JOIN pim_catalog_product product ON product.product_model_id = sub_product_model.id AND product.product_model_id IS NOT NULL
SQL;
    }

    private function joinToProductWithOneLevel(): string
    {
        return <<<SQL
                FROM pim_catalog_product_model AS root_product_model
                INNER JOIN pim_catalog_product product ON product.product_model_id = root_product_model.id AND product.product_model_id IS NOT NULL
SQL;
    }

    private function fetchResults(string $subquery, ProductModelInterface $productModel): CompleteVariantProducts
    {
        //The distinct is the reason why the query is fast
        //It helps the MySQL Optimizer to choose the right path
        //Otherwise we could have used the querybuilder
        $query = <<<SQL
            SELECT
                channel.code AS channel_code,
                locale.code AS locale_code,
                product.uuid as product_uuid,
                CASE WHEN (product.product_missing_count = 0) THEN 1 ELSE 0 END as complete
            FROM
              (
                SELECT DISTINCT product.uuid, completeness.locale_id, completeness.channel_id, completeness.missing_count as product_missing_count
                %s
                INNER JOIN pim_catalog_completeness completeness ON product.uuid = completeness.product_uuid
                WHERE
                    root_product_model.id = :root_product_model_id
              ) AS product
            INNER JOIN pim_catalog_locale locale ON locale.id = product.locale_id
            INNER JOIN pim_catalog_channel channel ON channel.id = product.channel_id
SQL;

        $query = sprintf($query, $subquery);

        $statement = $this->connection->prepare($query);

        $statement->bindValue('root_product_model_id', $productModel->getId());

        return new CompleteVariantProducts($statement->executeQuery()->fetchAllAssociative());
    }
}
