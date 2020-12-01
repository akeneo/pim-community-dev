<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Attributes
{
    private const LRU_CACHE_SIZE = 1000;

    private Connection $dbConnection;

    private LRUCache $attributeIdsByCodes;

    private LRUCache $attributeCodesByIds;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeIdsByCodes = new LRUCache(self::LRU_CACHE_SIZE);
        $this->attributeCodesByIds = new LRUCache(self::LRU_CACHE_SIZE);
    }

    public function getCodesByIds(array $attributesIds): array
    {
        // Because LRUCache can only be used with string keys
        $attributesIds = array_map(function ($attributeId) {
            return $this->castAttributeIdIntToString($attributeId);
        }, $attributesIds);

        $rawAttributesCodes = $this->attributeCodesByIds->getForKeys($attributesIds, function ($attributesIds) {
            $attributesIds = array_map(function ($attributeId) {
                return $this->castAttributeIdStringToInt($attributeId);
            }, $attributesIds);
            $attributesCodes = $this->dbConnection->executeQuery(
                "SELECT JSON_OBJECTAGG(CONCAT('a_', id), code) FROM pim_catalog_attribute WHERE id IN (:ids);",
                ['ids' => $attributesIds],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchColumn();

            return !$attributesCodes ? [] : json_decode($attributesCodes, true);
        });

        $attributesCodes = [];
        foreach ($rawAttributesCodes as $attributeId => $attributeCode) {
            $attributesCodes[$this->castAttributeIdStringToInt($attributeId)] = $attributeCode;
        }

        return $attributesCodes;
    }

    public function getIdsByCodes(array $attributesCodes): array
    {
        return $this->attributeIdsByCodes->getForKeys($attributesCodes, function ($attributesCodes) {
            $attributesIds = $this->dbConnection->executeQuery(
                'SELECT JSON_OBJECTAGG(code, id) FROM pim_catalog_attribute WHERE code IN (:codes);',
                ['codes' => $attributesCodes],
                ['codes' => Connection::PARAM_STR_ARRAY]
            )->fetchColumn();

            return !$attributesIds ? [] : json_decode($attributesIds, true);
        });
    }

    private function castAttributeIdIntToString(int $attributeId): string
    {
        return sprintf('a_%d', $attributeId);
    }

    private function castAttributeIdStringToInt(string $attributeId): int
    {
        return intval(substr($attributeId, 2));
    }
}
