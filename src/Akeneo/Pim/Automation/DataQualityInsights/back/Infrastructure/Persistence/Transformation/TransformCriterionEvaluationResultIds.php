<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultIds
{
    private const LRU_CACHE_SIZE = 1000;

    /** @var Connection */
    private $dbConnection;

    /** @var null|array */
    private $channelCodesByIds;

    /** @var null|array */
    private $localeCodesByIds;

    /** @var LRUCache */
    private $attributeCodesByIds;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeCodesByIds = new LRUCache(self::LRU_CACHE_SIZE);
    }

    public function transformToCodes(array $evaluationResult): array
    {
        $resultByCodes = [];
        $propertiesIds = TransformCriterionEvaluationResultCodes::PROPERTIES_ID;
        $propertiesCodes = array_flip($propertiesIds);

        foreach ($evaluationResult as $propertyId => $propertyData) {
            switch ($propertyId) {
                case $propertiesIds['data']:
                    $propertyDataByCodes = ['attributes_with_rates' => $this->transformResultAttributeRatesIdsToCodes($propertyData)];
                    break;
                case $propertiesIds['rates']:
                    $propertyDataByCodes = $this->transformRatesIdsToCodes($propertyData);
                    break;
                case $propertiesIds['status']:
                    $propertyDataByCodes = $this->transformStatusIdsToCodes($propertyData);
                    break;
                default:
                    throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown property id "%s"', $propertyId));
            }

            $resultByCodes[$propertiesCodes[$propertyId]] = $propertyDataByCodes;
        }

        return $resultByCodes;
    }

    private function transformChannelLocaleDataFromIdsToCodes(array $channelLocaleData, \Closure $transformData): array
    {
        $channelLocaleDataByCodes = [];

        foreach ($channelLocaleData as $channel => $localeData) {
            $channelCode = $this->getChannelCode($channel);
            if (null === $channelCode) {
                continue;
            }

            foreach ($localeData as $locale => $data) {
                $localeCode = $this->getLocaleCode($locale);
                if (null === $localeCode) {
                    continue;
                }

                $channelLocaleDataByCodes[$channelCode][$localeCode] = $transformData($data);
            }
        }

        return $channelLocaleDataByCodes;
    }

    private function transformResultAttributeRatesIdsToCodes(array $resultAttributeIdsRates): array
    {
        return $this->transformChannelLocaleDataFromIdsToCodes($resultAttributeIdsRates, function (array $attributeRates) {
            $attributeCodesRates = [];
            $attributesCodes = $this->getAttributesCodes(array_keys($attributeRates));

            foreach ($attributeRates as $attributeId => $attributeRate) {
                $attributeCode = $attributesCodes[$this->formatAttributeId($attributeId)] ?? null;
                if (null !== $attributeCode) {
                    $attributeCodesRates[$attributeCode] = $attributeRate;
                }
            }

            return $attributeCodesRates;
        });
    }

    private function transformRatesIdsToCodes(array $ratesIds): array
    {
        return $this->transformChannelLocaleDataFromIdsToCodes($ratesIds, function ($rate) {
            return $rate;
        });
    }

    private function transformStatusIdsToCodes(array $statusIds): array
    {
        $statusCodes = array_flip(TransformCriterionEvaluationResultCodes::STATUS_ID);
        return $this->transformChannelLocaleDataFromIdsToCodes($statusIds, function ($statusId) use ($statusCodes) {
            if (!isset($statusCodes[$statusId])) {
                throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown status id "%s"', $statusId));
            }

            return $statusCodes[$statusId];
        });
    }

    private function getChannelCode(int $id): ?string
    {
        if (null === $this->channelCodesByIds) {
            $this->loadChannels();
        }

        return $this->channelCodesByIds[$id] ?? null;
    }

    private function getLocaleCode(int $id): ?string
    {
        if (null === $this->localeCodesByIds) {
            $this->loadLocales();
        }

        return $this->localeCodesByIds[$id] ?? null;
    }

    private function getAttributesCodes(array $attributesIds): array
    {
        // Because LRUCache can only be used with string keys
        $attributesIds = $this->formatAttributesIds($attributesIds);

        return $this->attributeCodesByIds->getForKeys($attributesIds, function ($attributesIds) {
            $attributesIds = array_map(function ($attributeId) {
                return intval(substr($attributeId, 2));
            }, $attributesIds);
            $attributesCodes = $this->dbConnection->executeQuery(
                "SELECT JSON_OBJECTAGG(CONCAT('a_', id), code) FROM pim_catalog_attribute WHERE id IN (:ids);",
                ['ids' => $attributesIds],
                ['ids' => Connection::PARAM_INT_ARRAY]
            )->fetchColumn();

            return !$attributesCodes ? [] : json_decode($attributesCodes, true);
        });
    }

    private function loadChannels(): void
    {
        $this->channelCodesByIds = [];

        $channels = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_channel;'
        )->fetchColumn();

        if (false !== $channels) {
            $this->channelCodesByIds = json_decode($channels, true);
        }
    }

    private function loadLocales(): void
    {
        $this->localeCodesByIds = [];

        $locales = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_locale WHERE is_activated = 1;'
        )->fetchColumn();

        if (false !== $locales) {
            $this->localeCodesByIds = json_decode($locales, true);
        }
    }

    private function formatAttributeId(int $attributeId): string
    {
        return sprintf('a_%d', $attributeId);
    }

    private function formatAttributesIds(array $attributesIds): array
    {
        return array_map(function ($attributeId) {
            return $this->formatAttributeId($attributeId);
        }, $attributesIds);
    }
}
