<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConvertCriterionEvaluationResultCodes
{
    private const LRU_CACHE_SIZE = 1000;

    public const PROPERTIES_ID = [
        'data' => 1,
        'rates' => 2,
        'status' => 3,
    ];

    public const STATUS_ID = [
        CriterionEvaluationResultStatus::DONE => 1,
        CriterionEvaluationResultStatus::IN_PROGRESS => 2,
        CriterionEvaluationResultStatus::ERROR => 3,
        CriterionEvaluationResultStatus::NOT_APPLICABLE => 4,
    ];

    /** @var Connection */
    private $dbConnection;

    /** @var null|array */
    private $channelIdsByCodes;

    /** @var null|array */
    private $localeIdsByCodes;

    /** @var LRUCache */
    private $attributeIdsByCodes;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeIdsByCodes = new LRUCache(self::LRU_CACHE_SIZE);
    }

    public function convertToIds(array $evaluationResult): array
    {
        $resultByIds = [];

        foreach ($evaluationResult as $propertyCode => $propertyData) {
            switch ($propertyCode) {
                case 'data':
                    $propertyDataByIds = $this->convertResultAttributeRatesCodesToIds($propertyData);
                    break;
                case 'rates':
                    $propertyDataByIds = $this->convertRatesCodesToIds($propertyData);
                    break;
                case 'status':
                    $propertyDataByIds = $this->convertStatusCodesToIds($propertyData);
                    break;
                default:
                    throw new CriterionEvaluationResultConversionFailedException(sprintf('Unknown property code "%s"', $propertyCode));
            }

            $resultByIds[self::PROPERTIES_ID[$propertyCode]] = $propertyDataByIds;
        }

        return $resultByIds;
    }

    private function convertChannelLocaleDataFromCodesToIds(array $channelLocaleData, \Closure $convertData): array
    {
        $channelLocaleDataByIds = [];

        foreach ($channelLocaleData as $channel => $localeData) {
            $channelId = $this->getChannelId($channel);
            if (null === $channelId) {
                continue;
            }

            foreach ($localeData as $locale => $data) {
                $localeId = $this->getLocaleId($locale);
                if (null === $localeId) {
                    continue;
                }

                $channelLocaleDataByIds[$channelId][$localeId] = $convertData($data);
            }
        }

        return $channelLocaleDataByIds;
    }

    private function convertResultAttributeRatesCodesToIds(array $resultAttributeCodesRates): array
    {
        $resultAttributeCodesRates = $resultAttributeCodesRates['attributes_with_rates'] ?? [];

        return $this->convertChannelLocaleDataFromCodesToIds($resultAttributeCodesRates, function (array $attributeRates) {
            $attributeIdsRates = [];
            $attributesIds = $this->getAttributesIds(array_keys($attributeRates));

            foreach ($attributeRates as $attributeCode => $attributeRate) {
                $attributeId = $attributesIds[$attributeCode] ?? null;
                if (null !== $attributeId) {
                    $attributeIdsRates[$attributeId] = $attributeRate;
                }
            }

            return $attributeIdsRates;
        });
    }

    private function convertRatesCodesToIds(array $ratesCodes): array
    {
        return $this->convertChannelLocaleDataFromCodesToIds($ratesCodes, function ($rate) {
            return $rate;
        });
    }

    private function convertStatusCodesToIds(array $statusCodes): array
    {
        return $this->convertChannelLocaleDataFromCodesToIds($statusCodes, function (string $statusCode) {
            if (!isset(self::STATUS_ID[$statusCode])) {
                throw new CriterionEvaluationResultConversionFailedException(sprintf('Unknown status code "%s"', $statusCode));
            }

            return self::STATUS_ID[$statusCode];
        });
    }

    private function getChannelId(string $code): ?int
    {
        if (null === $this->channelIdsByCodes) {
            $this->loadChannels();
        }

        return $this->channelIdsByCodes[$code] ?? null;
    }

    private function loadChannels(): void
    {
        $this->channelIdsByCodes = [];

        $channels = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(code, id) FROM pim_catalog_channel;'
        )->fetchColumn();

        if (false !== $channels) {
            $this->channelIdsByCodes = json_decode($channels, true);
        }
    }

    private function getLocaleId(string $code): ?int
    {
        if (null === $this->localeIdsByCodes) {
            $this->loadLocales();
        }

        return $this->localeIdsByCodes[$code] ?? null;
    }


    private function loadLocales(): void
    {
        $this->localeIdsByCodes = [];

        $locales = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(code, id) FROM pim_catalog_locale WHERE is_activated = 1;'
        )->fetchColumn();

        if (false !== $locales) {
            $this->localeIdsByCodes = json_decode($locales, true);
        }
    }

    private function getAttributesIds(array $attributesCodes): array
    {
        return $this->attributeIdsByCodes->getForKeys($attributesCodes, function ($attributesCodes) {
            $attributesIds = $this->dbConnection->executeQuery(
                'SELECT JSON_OBJECTAGG(code, id) FROM pim_catalog_attribute WHERE code IN (:codes);',
                ['codes' => $attributesCodes],
                ['codes' => Connection::PARAM_STR_ARRAY]
            )->fetchColumn();

            return false === $attributesIds ? [] : json_decode($attributesIds, true);
        });
    }
}
