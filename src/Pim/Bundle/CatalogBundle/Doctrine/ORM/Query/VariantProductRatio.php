<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\CompleteVariantProducts;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;

/**
 * Query variant product completenesses to build the complete variant product ratio on the PMEF
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductRatio implements VariantProductRatioInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * VariantProductRatio constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $channel
     * @param string                $locale
     *
     * @return CompleteVariantProducts
     */
    public function findComplete(
        ProductModelInterface $productModel,
        string $channel = '',
        string $locale = ''
    ): CompleteVariantProducts {
        $join = null;

        if (2 === $productModel->getFamilyVariant()->getNumberOfLevel() && $productModel->isRootProductModel()) {
            $join = $this->joinToProductWithTwoLevels();
        } else {
            $join = $this->joinToProductWithOneLevel();
        }

        return $this->fetchResults($join, $productModel, $channel, $locale);
    }

    private function joinToProductWithTwoLevels(): string
    {
        return <<<SQL
                FROM pim_catalog_product_model AS root_product_model
                INNER JOIN pim_catalog_product_model as sub_product_model ON sub_product_model.parent_id = root_product_model.id
                INNER JOIN pim_catalog_product product ON product.product_model_id = sub_product_model.id
SQL;
    }

    private function joinToProductWithOneLevel(): string
    {
        return <<<SQL
                FROM pim_catalog_product_model AS root_product_model
                INNER JOIN pim_catalog_product product ON product.product_model_id = root_product_model.id
SQL;
    }

    private function fetchResults(string $subquery, ProductModelInterface $productModel, string $channel = '', string $locale = ''): CompleteVariantProducts
    {
        //The distinct is the reason why the query is fast
        //It helps the MySQL Optimizer to choose the right path
        //Otherwise we could have used the querybuilder
        $query = <<<SQL
            SELECT channel.code AS channel_code, locale.code AS locale_code, product.identifier as product_identifier, CASE WHEN (product.product_ratio = 100) THEN 1 ELSE 0 END as complete
            FROM
              (
                SELECT DISTINCT product.identifier, completeness.locale_id, completeness.channel_id, completeness.ratio as product_ratio
                %s
                INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
                WHERE
                    root_product_model.id = :root_product_model_id
              ) AS product
            INNER JOIN pim_catalog_locale locale ON locale.id = product.locale_id
            INNER JOIN pim_catalog_channel channel ON channel.id = product.channel_id
SQL;

        $query = sprintf($query, $subquery);
        $parameters = [];
        $parameters[] = ['name' => 'root_product_model_id', 'value' => $productModel->getId()];

        if (!empty($channel) && !empty($locale)) {
            $query .= <<<SQL
            WHERE locale.code = :locale AND channel.code = :channel
SQL;
            $parameters[] = ['name' => 'channel', 'value' => $channel];
            $parameters[] = ['name' => 'locale', 'value' => $locale];
        } else {
            if (!empty($locale)) {
                $query .= <<<SQL
                    WHERE locale.code = :locale
SQL;
                $parameters[] = ['name' => 'locale', 'value' => $locale];
            }
            if (!empty($channel)) {
                $query .= <<<SQL
                    WHERE channel.code = :channel
SQL;
                $parameters[] = ['name' => 'channel', 'value' => $channel];
            }
        }

        $statement = $this->entityManager->getConnection()->prepare($query);

        foreach ($parameters as $parameter) {
            $statement->bindValue($parameter['name'], $parameter['value']);
        }

        $statement->execute();

        return new CompleteVariantProducts($statement->fetchAll());
    }
}
