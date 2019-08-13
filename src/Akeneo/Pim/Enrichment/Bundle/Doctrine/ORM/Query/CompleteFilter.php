<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterData;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Find data used by the datagrid completeness filter. We need to know if a product model has at least one
 * complete / incomplete variant product for each channel and locale.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteFilter implements CompleteFilterInterface
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
    public function findCompleteFilterData(ProductModelInterface $productModel): CompleteFilterData
    {
        if (2 === $productModel->getFamilyVariant()->getNumberOfLevel() && $productModel->isRootProductModel()) {
            $sql = <<<SQL
SELECT
    channel.code AS channel_code,
    locale.code AS locale_code,
    CASE WHEN (completeness.ratio = 100) THEN 1 ELSE 0 END AS complete, 
    CASE WHEN (completeness.ratio < 100) THEN 1 ELSE 0 END AS incomplete
FROM pim_catalog_product_model root_product_model
INNER JOIN pim_catalog_product_model sub_product_model ON root_product_model.id = sub_product_model.parent_id
INNER JOIN pim_catalog_product product ON sub_product_model.id = product.product_model_id
INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
WHERE root_product_model.id = :product_model_id
SQL;
        } else {
            $sql = <<<SQL
SELECT
    channel.code AS channel_code,
    locale.code AS locale_code,
    CASE WHEN (completeness.ratio = 100) THEN 1 ELSE 0 END AS complete, 
    CASE WHEN (completeness.ratio < 100) THEN 1 ELSE 0 END AS incomplete
FROM pim_catalog_product_model root_product_model
INNER JOIN pim_catalog_product product ON root_product_model.id = product.product_model_id
INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
WHERE root_product_model.id = :product_model_id
SQL;
        }

        $data = $this->connection->executeQuery($sql, [
            'product_model_id' => $productModel->getId()
        ])->fetchAll();

        return new CompleteFilterData($data);
    }
}
