<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ValuesFilter;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\AttributeTypes;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingSimpleSelectOptionFilter implements StorageFormatFilter, BatchStorageFormatFilter
{
    /** @var Connection */
    private $connection;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Connection      $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMany(array $rawValuesList): array
    {
        $attributeCodes = $this->getAttributeCodes($rawValuesList);
        $simpleSelectAttributeCodes = $this->getSimpleSelectAttributeCodes($attributeCodes);
        $existingOptionCodes = $this->getOptionCodes($rawValuesList, $simpleSelectAttributeCodes);

        $filteredListOfRawValues = $rawValuesList;
        foreach ($filteredListOfRawValues as $rawValuesKey => $rawValues) {
            foreach ($rawValues as $attributeCode => $valuePerLocale) {
                if (isset($simpleSelectAttributeCodes[$attributeCode])) {
                    foreach ($valuePerLocale as $locale => $valuesPerChannel) {
                        foreach ($valuesPerChannel as $channel=> $value) {
                            if (isset($existingOptionCodes)) {
                                unset($filtere)
                            }
                        }
                    }
                }
            }
        }

        return $filteredListOfRawValues;
    }

    public function filterSingle(array $rawValues): array
    {
        $filteredRawValuesList = $this->filterMany([$rawValues]);

        return $filteredRawValuesList[0] ?? [];
    }

    /**
     * @param array $rawValuesList
     *
     * @return array
     */
    private function getAttributeCodes(array $rawValuesList): array
    {
        $attributeCodes = [];

        foreach ($rawValuesList as $rawValues) {
            $attributeCodes = array_merge($attributeCodes, array_keys($rawValues));
        }

        return array_unique($attributeCodes);
    }

    /**
     * @param array $attributeCodes
     *
     * @return array indexed by attribute code
     *
     */
    private function getSimpleSelectAttributeCodes(array $attributeCodes): array
    {
        $sql = <<<SQL
            SELECT code 
            FROM pim_catalog_attribute
            WHERE code IN (:attribute_codes)
            AND attribute_type = :attribute_types
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['attribute_codes' => $attributeCodes],
            [
                'attribute_types' => [
                    AttributeTypes::OPTION_SIMPLE_SELECT
                ]
            ],
            [
                'attribute_codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            ]
        )->fetchAll();

        $existingAttributeCodes = [];
        foreach ($rows as $row) {
            $existingAttributeCodes[$row['code']] = $row['code'];
        }

        return $existingAttributeCodes;
    }

    /**
     * @param array $rawValuesList
     * @param array $simpleSelectAttributeCodes
     *
     * @return array
     */
    private function getOptionCodes(array $rawValuesList, array $simpleSelectAttributeCodes)
    {
        $optionCodes = [];
        foreach ($rawValuesList as $rawValues) {
            foreach ($rawValues as $attributeCode => $valuePerChannel) {
                if (isset($simpleSelectAttributeCodes[$attributeCode])) {
                    foreach ($valuePerChannel as $locale => $valuesPerLocale) {
                        foreach ($valuesPerLocale as $value) {
                            if (isset($value['data']) {
                                $optionCode[$value['data']] = $value['data'];
                            }
                        }
                    }
                }
            }
        }

        return $optionCodes;
    }
}
