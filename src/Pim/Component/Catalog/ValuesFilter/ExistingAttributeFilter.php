<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ValuesFilter;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingAttributeFilter implements StorageFormatFilter, BatchStorageFormatFilter
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
        $existingAttributeCodes = $this->getExistingAttributeCodes($attributeCodes);

        $filteredListOfRawValues = [];
        foreach ($rawValuesList as $rawValues) {
            $filteredListOfRawValues[] = array_filter(
                $rawValues,
                function (string $attributeCode) use ($existingAttributeCodes) {
                    $hasAttribute = isset($existingAttributeCodes[$attributeCode]);

                    if (!$hasAttribute) {
                        $this->logger->warning(
                            sprintf(
                                'Tried to load a product value with the attribute "%s" that does not exist.',
                                $attributeCode
                            )
                        );
                    }

                    return $hasAttribute;
                },
                ARRAY_FILTER_USE_KEY
            );
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
    private function getExistingAttributeCodes(array $attributeCodes): array
    {
        $sql = <<<SQL
            SELECT code 
            FROM pim_catalog_attribute
            WHERE code IN (:attribute_codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['attribute_codes' => $attributeCodes],
            ['attribute_codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $existingAttributeCodes = [];
        foreach ($rows as $row) {
            $existingAttributeCodes[$row['code']] = $row['code'];
        }

        return $existingAttributeCodes;
    }
}
