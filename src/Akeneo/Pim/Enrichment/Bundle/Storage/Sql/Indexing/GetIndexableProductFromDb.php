<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\IndexableProduct;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetIndexableProductInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIndexableProductFromDb implements GetIndexableProductInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $productIdentifier
     * @return IndexableProduct|null
     */
    public function fromProductIdentifier(string $productIdentifier): ?IndexableProduct
    {
        return current($this->fromProductIdentifiers([$productIdentifier]));
    }

    /**
     * @param array $productIdentifiers
     * @return IndexableProduct[]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        // @todo: in 2 or 3 (more?) queries
        $mainResults = $this->indexAssocArrayByColumn($this->mainQuery($productIdentifiers), 'identifier');

        $results = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $mainResult = $mainResults[$productIdentifier];

            $rawValues = $mainResult['raw_values'];

            $results[$productIdentifier] = new IndexableProduct(
                $mainResult['id'],
                $mainResult['identifier'],
                new \DateTimeImmutable($mainResult['created']),
                new \DateTimeImmutable($mainResult['updated']),
                $mainResult['enabled'] === 1,
                $mainResult['family_code'],
                $mainResult['family_labels'],
                $mainResult['family_variant_code'],
                $mainResult['category_codes'],
                ['todo category_codes_of_ancestors'],
                $mainResult['group_codes'],
                ['todo completeness'],
                $mainResult['parent_identifier'],
                $mainResult['raw_values'], // @todo: normalize (it was the normalizer of WriteValueCollection before)
                ['todo ancestors'],
                $rawValues[$mainResult['label_attribute_code']] ?? null
            );
        }

        return $results;
    }

    protected function mainQuery(array $productIdentifiers): array
    {
        $sql = <<<SQL
WITH
filter_product AS (
  SELECT id, identifier, product_model_id AS parent_id FROM pim_catalog_product WHERE identifier IN (:identifiers)
),
parent_product AS (
  WITH RECURSIVE parent_product_recursive (id, parent_id, parent_level) AS (
    SELECT
      p.id,
      p.parent_id,
      pcpm.lvl AS parent_level
    FROM filter_product p
      INNER JOIN pim_catalog_product_model pcpm ON pcpm.id = p.parent_id
  UNION ALL
    SELECT
      ppr.id    AS id,
      pcpm2.id  AS parent_id,
      pcpm2.lvl AS parent_level
    FROM parent_product_recursive ppr
      INNER JOIN pim_catalog_product_model pcpm  ON pcpm.id = ppr.parent_id
      INNER JOIN pim_catalog_product_model pcpm2 ON pcpm2.id = pcpm.parent_id
  )
  SELECT * FROM parent_product_recursive
),
product_last_updated AS (
    SELECT
      fp.id            AS id,
      MAX(pcp.updated) AS last_updated
    FROM filter_product fp
      LEFT JOIN parent_product pp  ON pp.id = fp.id
      JOIN pim_catalog_product pcp ON pcp.id = fp.id OR pcp.id = pp.parent_id
    GROUP BY fp.id
),
product_family AS (
  SELECT
    fp.id,
    pcf.code,
    COALESCE(JSON_ARRAYAGG(JSON_OBJECT(pcft.locale, pcft.label)), JSON_ARRAY()) AS labels
  FROM filter_product fp
    INNER JOIN pim_catalog_product pcp            ON pcp.id = fp.id
    INNER JOIN pim_catalog_family pcf             ON pcf.id = pcp.family_id
    LEFT JOIN pim_catalog_family_translation pcft ON pcft.foreign_key = pcf.id
  GROUP BY fp.id, pcf.code
),
product_categories AS (
  SELECT
    fp.id,
    JSON_ARRAYAGG(pcc.code) AS category_codes
  FROM filter_product fp
    JOIN pim_catalog_category_product pccp ON pccp.product_id = fp.id
    JOIN pim_catalog_category pcc          ON pcc.id = pccp.category_id
  GROUP BY fp.id
),
parent_categories AS (
  SELECT
    fp.id,
    JSON_ARRAYAGG(pcc.code) AS category_codes
  FROM filter_product fp
    JOIN pim_catalog_category_product pccp ON pccp.product_id = fp.parent_id
    JOIN pim_catalog_category pcc          ON pcc.id = pccp.category_id
  GROUP BY fp.id
),
product_groups AS (
  SELECT
    fp.id,
    JSON_ARRAYAGG(pcg.code) AS group_codes
  FROM filter_product fp
  JOIN pim_catalog_group_product pcgp ON pcgp.product_id = fp.id
  JOIN pim_catalog_group pcg          ON pcg.id = pcgp.group_id
  GROUP BY fp.id
),
product_values AS (
    -- TODO: test it!!
    -- Explain: we merge all values (priority values are those at higher level) until we come to level 0.
    -- At level 0 we have the computed values for the product.
  WITH RECURSIVE parent_product_recursive (id, parent_id, parent_level, computed_values) AS (
    SELECT
      p.id,
      p.parent_id,
      pcpm.lvl AS parent_level,
      JSON_MERGE_PATCH(pcpm.raw_values, pcp.raw_values) AS computed_values
    FROM filter_product p
      INNER JOIN pim_catalog_product pcp ON pcp.id = p.id
      INNER JOIN pim_catalog_product_model pcpm ON pcpm.id = p.parent_id
  UNION ALL
    SELECT
      ppr.id    AS id,
      pcpm2.id  AS parent_id,
      pcpm2.lvl AS parent_level,
      JSON_MERGE_PATCH(pcpm2.raw_values, ppr.computed_values) AS computed_values
    FROM parent_product_recursive ppr
      INNER JOIN pim_catalog_product_model pcpm  ON pcpm.id = ppr.parent_id
      INNER JOIN pim_catalog_product_model pcpm2 ON pcpm2.id = pcpm.parent_id
  )
  SELECT id, parent_id, parent_level, computed_values FROM parent_product_recursive
)
SELECT
  pcp.id,
  pcp.identifier,
  DATE_FORMAT(pcp.created, '%Y-%m-%dT%TZ') AS created,
  DATE_FORMAT(plu.last_updated, '%Y-%m-%dT%TZ') AS updated,
  IF(pfa.id IS NULL, NULL,JSON_OBJECT('code', pfa.code, 'labels', pfa.labels)) AS family,
  pcp.is_enabled AS enabled,
  COALESCE(pc.category_codes, JSON_ARRAY()) AS categories,
  COALESCE(pac.category_codes, JSON_ARRAY()) AS categories_of_ancestors,
  'todo in another query?' AS completeness,
  COALESCE(pg.group_codes, JSON_ARRAY()) AS group_codes,
  pcfv.code AS family_variant,
  fp.parent_id AS parent,
  COALESCE(pv.computed_values, pcp.raw_values) AS computed_values,
  'todo' AS ancestors,
  pca.code                                      AS label_attribute_code,
  'todo' AS attributes_of_ancestors,
  'todo' AS attributes_for_this_level
FROM filter_product fp
  INNER JOIN pim_catalog_product pcp         ON pcp.id = fp.id
  INNER JOIN product_last_updated plu        ON plu.id = fp.id
  LEFT JOIN  product_family pfa              ON pfa.id = fp.id
  LEFT JOIN  product_categories pc           ON pc.id = fp.id
  LEFT JOIN  parent_categories pac           ON pac.id = fp.id
  LEFT JOIN  product_groups pg               ON pg.id = fp.id
  LEFT JOIN  pim_catalog_family_variant pcfv ON pcfv.id = pcp.family_variant_id
  LEFT JOIN  pim_catalog_family pcf          ON pcf.id = pcp.family_id
  LEFT JOIN  pim_catalog_attribute pca       ON pca.id = pcf.label_attribute_id
  LEFT JOIN  product_values pv               ON pv.id = fp.id AND pv.parent_level = 0
;
SQL;

        return $this
            ->connection
            ->executeQuery($sql, ['identifiers' => $productIdentifiers])
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param array  $rows
     * @param string $column
     * @return array
     */
    protected function indexAssocArrayByColumn(array $rows, string $column): array
    {
        $results = [];
        foreach ($rows as $row) {
            $results[$row[$column]] = $row;
        }

        return $results;
    }
}
