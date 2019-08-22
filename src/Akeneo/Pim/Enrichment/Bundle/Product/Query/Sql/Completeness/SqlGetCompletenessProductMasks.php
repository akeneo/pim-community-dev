<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGenerator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenessProductMasks implements GetCompletenessProductMasks
{
    /** @var Connection */
    private $connection;

    /** @var MaskItemGenerator */
    private $maskItemGenerator;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var EmptyValuesCleaner */
    private $emptyValuesCleaner;

    public function __construct(
        Connection $connection,
        MaskItemGenerator $maskItemGenerator,
        GetAttributes $getAttributes,
        EmptyValuesCleaner $emptyValuesCleaner
    ) {
        $this->connection = $connection;
        $this->maskItemGenerator = $maskItemGenerator;
        $this->getAttributes = $getAttributes;
        $this->emptyValuesCleaner = $emptyValuesCleaner;
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return CompletenessProductMask[]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        // TODO - TIP-1212: Replace the first LEFT JOIN (to pim_catalog_family) by an INNER JOIN
        $sql = <<<SQL
SELECT
    product.id AS id,
    product.identifier AS identifier,
    family.code AS familyCode,
    JSON_MERGE(
           COALESCE(pm1.raw_values, '{}'),
           COALESCE(pm2.raw_values, '{}'),
           product.raw_values
    ) AS rawValues
FROM pim_catalog_product product
    LEFT JOIN pim_catalog_family family ON product.family_id = family.id
    LEFT JOIN pim_catalog_product_model pm1 ON product.product_model_id = pm1.id
    LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
WHERE product.identifier IN (:productIdentifiers)
GROUP BY product.identifier
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $attributeCodes = [];
        $rowsWithCleanedRawValues = [];

        foreach ($rows as $row) {
            $rowsWithCleanedRawValue = $row;
            $rowsWithCleanedRawValue['cleanedRawValues'] = $this->cleanEmptyValues(json_decode($rowsWithCleanedRawValue['rawValues'], true));
            foreach (array_keys($rowsWithCleanedRawValue['cleanedRawValues']) as $attributeCode) {
                $attributeCodes[] = $attributeCode;
            }
            $rowsWithCleanedRawValues[] = $rowsWithCleanedRawValue;
        }

        $attributes = $this->getAttributes->forCodes(array_unique($attributeCodes));

        $result = [];
        foreach ($rowsWithCleanedRawValues as $row) {
            $result[] = new CompletenessProductMask(
                intval($row['id']),
                $row['identifier'],
                $row['familyCode'],
                $this->getMask($row['cleanedRawValues'], $attributes)
            );
        }

        return $result;
    }

    /**
     * @param array       $rawValues
     * @param Attribute[] $attributes
     *
     * @return string[]
     */
    private function getMask($rawValues, array $attributes): array
    {
        $masks = [];
        foreach ($rawValues as $attributeCode => $valuesByChannel) {
            if (!isset($attributes[$attributeCode])) {
                continue;
            }
            $attributeType = $attributes[$attributeCode]->type();
            foreach ($valuesByChannel as $channelCode => $valuesByLocale) {
                foreach ($valuesByLocale as $localeCode => $value) {
                    $masks[] = $this->maskItemGenerator->generate(
                        (string) $attributeCode,
                        $attributeType,
                        $channelCode,
                        $localeCode,
                        $value
                    );
                }
            }
        }

        return array_merge(...$masks);
    }

    private function cleanEmptyValues(array $rawValues): array
    {
        return $this->emptyValuesCleaner->cleanAllValues(['ID' => $rawValues])['ID'];
    }
}
