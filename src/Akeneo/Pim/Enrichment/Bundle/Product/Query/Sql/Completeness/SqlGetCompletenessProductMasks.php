<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProducts;
use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenessProductMasks implements GetProducts
{
    /** @var Connection */
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return CompletenessProductMask[]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
SELECT
    product.id AS id,
    product.raw_values AS rawValues,
    family.code AS familyCode,
    product.identifier AS identifier
FROM pim_catalog_product product
INNER JOIN pim_catalog_family family ON product.family_id=family.id
WHERE product.identifier IN (:productIdentifiers)
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = new CompletenessProductMask(
                intval($row['id']),
                $row['identifier'],
                $row['familyCode'],
                $this->getMask(json_decode($row['rawValues'], true))
            );
        }

        return $result;
    }

    /**
     * @param array $rawValues
     *
     * @return string[]
     */
    private function getMask($rawValues): array
    {
        foreach ($rawValues as $attributeCode => $valuesByChannel) {
            foreach ($valuesByChannel as $channelCode => $valuesByLocale) {
                foreach ($valuesByLocale as $localeCode => $value) {
                    // TODO Add Registry
                    // TODO This does not work for null metrics, e.g. "{"unit":null,"amount":null,"family":"Length","base_data":null,"base_unit":"METER"}"
                    if (null !== $value) {
                        $mask = sprintf(
                            '%s-%s-%s',
                            $this->formatAttributeCode($attributeCode, $value),
                            $channelCode,
                            $localeCode
                        );
                        $result[] = $mask;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * TODO Put this in a Registry to allow specific masks for new attribute types
     *
     * Case when $value is like
     * [{"amount": "2.00", "currency": "EUR"}, {"amount": "3.00", "currency": "USD"}]
     *
     * The currencies are sorted because the family masks are sorted too.
     *
     * @param string $attributeCode
     * @param mixed  $value
     *
     * @return string
     */
    private function formatAttributeCode(string $attributeCode, $value)
    {
        if (is_array($value)) {
            $isPrice = true;
            $currencies = [];
            foreach ($value as $v) {
                if (!is_array($v) || !isset($v['amount']) || !isset($v['currency'])) {
                    $isPrice = false;
                } else {
                    $currencies[] = $v['currency'];
                }
            }
            sort($currencies);

            if ($isPrice) {
                return $attributeCode . '-' . join('-', $currencies);
            }
        }

        return $attributeCode;
    }
}
