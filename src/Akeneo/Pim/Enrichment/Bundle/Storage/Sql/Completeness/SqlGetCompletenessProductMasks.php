<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGenerator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

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

    /** @var NormalizerInterface */
    private $valuesNormalizer;

    public function __construct(
        Connection $connection,
        MaskItemGenerator $maskItemGenerator,
        GetAttributes $getAttributes,
        NormalizerInterface $valuesNormalizer
    ) {
        $this->connection = $connection;
        $this->maskItemGenerator = $maskItemGenerator;
        $this->getAttributes = $getAttributes;
        $this->valuesNormalizer = $valuesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $productUuids): array
    {
        // TODO - TIP-1212: Replace the first LEFT JOIN (to pim_catalog_family) by an INNER JOIN
        // PIM-9783: the initial query didn't use the CTE, we filtered directly the product in the main SELECT. There
        // was some performance issues with a big number of productIdentifier. The CTE allows to fix it (please check
        // the issue for further information).
        $sql = <<<SQL
WITH
filtered_product AS (
    SELECT uuid FROM pim_catalog_product WHERE uuid IN (:productUuids)
)
SELECT
    BIN_TO_UUID(product.uuid) AS uuid,
    product.identifier AS identifier,
    family.code AS familyCode,
    JSON_MERGE(
           COALESCE(pm1.raw_values, '{}'),
           COALESCE(pm2.raw_values, '{}'),
           product.raw_values
    ) AS rawValues
FROM filtered_product
    INNER JOIN pim_catalog_product product ON filtered_product.uuid = product.uuid
    LEFT JOIN pim_catalog_family family ON product.family_id = family.id
    LEFT JOIN pim_catalog_product_model pm1 ON product.product_model_id = pm1.id
    LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
SQL;

        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $rows = array_map(
            function (array $row): array {
                return [
                    'id' => $row['uuid'],
                    'familyCode' => $row['familyCode'],
                    'cleanedRawValues' => json_decode($row['rawValues'], true),
                ];
            },
            $this->connection->executeQuery(
                $sql,
                ['productUuids' => $productUuidsAsBytes],
                ['productUuids' => Connection::PARAM_STR_ARRAY]
            )->fetchAllAssociative()
        );

        return $this->buildProductMasks($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function fromValueCollection(
        $id,
        string $familyCode,
        WriteValueCollection $values
    ): CompletenessProductMask {
        Assert::true(\is_int($id) || \is_string($id));
        $row = [
            'id' => $id,
            'familyCode' => $familyCode,
            'cleanedRawValues' => $this->valuesNormalizer->normalize($values, 'storage'),
        ];

        return $this->buildProductMasks([$row])[0];
    }

    private function buildProductMasks(array $rows): array
    {
        $attributeCodes = [];
        foreach ($rows as $row) {
            // array_unique is important for big catalog (see PIM-9783), because for a very high number of rows the array_merge takes too much time.
            // For instance for 1.000 rows it can take 1 second, for 10.000 rows more than 1 minute.
            // With array_unique it's less than 1 second in both cases.
            $attributeCodes = array_unique(array_merge($attributeCodes, array_keys($row['cleanedRawValues'])));
        }
        $attributes = $this->getAttributes->forCodes($attributeCodes);

        $result = [];
        foreach ($rows as $row) {
            $result[] = new CompletenessProductMask(
                (string) $row['id'],
                $row['familyCode'],
                $this->getMask($row['cleanedRawValues'], $attributes)
            );
        }

        return $result;
    }

    /**
     * @param array $rawValues
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

        if (empty($masks)) {
            return [];
        }

        return array_merge(...$masks);
    }
}
