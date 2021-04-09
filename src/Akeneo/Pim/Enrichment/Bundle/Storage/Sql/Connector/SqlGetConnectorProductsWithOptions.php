<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetConnectorProductsWithOptions implements Query\GetConnectorProducts
{
    /** @var Query\GetConnectorProducts */
    private $getConnectorProducts;

    /** @var Connection */
    private $connection;

    public function __construct(
        Query\GetConnectorProducts $getConnectorProducts,
        Connection $connection
    ) {
        $this->getConnectorProducts = $getConnectorProducts;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $pqb,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $connectorProductList = $this->getConnectorProducts->fromProductQueryBuilder($pqb, $userId, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn);
        $productsWithOptions = $this->getConnectorProductsWithLabels($connectorProductList->connectorProducts());

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithOptions);
    }

    public function fromProductIdentifier(string $productIdentifier, int $userId): ConnectorProduct
    {
        $connectorProduct = $this->getConnectorProducts = $this->getConnectorProducts->fromProductIdentifier($productIdentifier, $userId);

        return $this->getConnectorProductsWithLabels([$connectorProduct])[0];
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductIdentifiers(
        array $productIdentifiers,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $connectorProductList = $this->getConnectorProducts->fromProductIdentifiers($productIdentifiers, $userId, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn);
        $productsWithOptions = $this->getConnectorProductsWithLabels($connectorProductList->connectorProducts());

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithOptions);
    }

    private function getConnectorProductsWithLabels(array $connectorProducts): array
    {
        $optionCodes = $this->getOptionCodes($connectorProducts);
        $optionWithLabels = $this->getOptionWithLabels($optionCodes);

        return array_map(function (ConnectorProduct $product) use ($optionWithLabels) {
            return $product->buildLinkedData($optionWithLabels);
        }, $connectorProducts);
    }

    /**
     * @param array $connectorProducts
     * @return array{'attribute_code': int, 'option_code': mixed|string}
     */
    private function getOptionCodes(array $connectorProducts): array
    {
        $optionCodes = [];
        foreach ($connectorProducts as $connectorProduct) {
            foreach ($connectorProduct->values() as $value) {
                if ($value instanceof OptionValue) {
                    $optionCodes[] = [$value->getAttributeCode(), $value->getData()];
                } elseif ($value instanceof OptionsValue) {
                    foreach ($value->getData() as $optionCode) {
                        $optionCodes[] = [$value->getAttributeCode(), $optionCode];
                    }
                }
            }
        }

        return $optionCodes;
    }

    /**
     * @param array $optionCodes [['attribute_code', 'option_code']]
     *
     * @return array ['attribute_code' => ['option_code' => ['en_US' => 'translation']]
     */
    private function getOptionWithLabels(array $optionCodes): array
    {
        if (empty($optionCodes)) {
            return [];
        }

        $queryStringParams = array_fill(0, count($optionCodes), '(?, ?)');

        $queryParams = [];
        foreach ($optionCodes as [$attributeCode, $optionCode]) {
            $queryParams[] = $attributeCode;
            $queryParams[] = $optionCode;
        }

        $query = <<<SQL
            WITH option_values AS (
                SELECT
                    a.code AS attribute_code,
                    ao.code AS option_code,
                    JSON_OBJECTAGG(aov.locale_code, aov.value) AS option_values
                FROM pim_catalog_attribute a
                JOIN pim_catalog_attribute_option ao ON  ao.attribute_id = a.id
                JOIN pim_catalog_attribute_option_value aov ON aov.option_id = ao.id
                WHERE (a.code, ao.code) IN (%s)
                GROUP BY attribute_code, ao.code
            ),
            aggregated_option_per_attribute AS (
                SELECT 
                    attribute_code,
                    JSON_OBJECTAGG(option_code, option_values) as option_values
                FROM option_values
                GROUP BY attribute_code
            ),
            aggregated_attributes AS (
                SELECT 
                    JSON_OBJECTAGG(attribute_code, option_values) as result
                FROM
                    aggregated_option_per_attribute
            )
            SELECT * FROM aggregated_attributes
            ;
        SQL;

        $row = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetch();


        return isset($row['result']) ? json_decode($row['result'], true) : [];
    }
}
