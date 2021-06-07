<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Analytics;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AggregatedAverageMaxNumberOfValuesPerRecord;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlLocalizableOnly;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlScopableAndLocalizable;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity\SqlScopableOnly;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerReferenceEntity;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfRecordsPerReferenceEntity;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlCountReferenceEntities;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityStatisticsCollector implements DataCollectorInterface
{
    /** @var SqlCountReferenceEntities */
    private $countReferenceEntities;

    /** @var SqlAverageMaxNumberOfRecordsPerReferenceEntity */
    private $averageMaxNumberOfRecordsPerReferenceEntity;

    /** @var AggregatedAverageMaxNumberOfValuesPerRecord */
    private $averageMaxNumberOfValuesPerRecord;

    /** @var SqlAverageMaxNumberOfAttributesPerReferenceEntity */
    private $averageMaxNumberOfAttributesPerReferenceEntity;

    /** @var SqlLocalizableOnly */
    private $localizableOnly;

    /** @var SqlScopableOnly */
    private $scopableOnly;

    /** @var SqlScopableAndLocalizable */
    private $scopableAndLocalizable;

    public function __construct(
        SqlCountReferenceEntities $countReferenceEntities,
        SqlAverageMaxNumberOfRecordsPerReferenceEntity $averageMaxNumberOfRecordsPerReferenceEntity,
        AggregatedAverageMaxNumberOfValuesPerRecord $averageMaxNumberOfValuesPerRecord,
        SqlAverageMaxNumberOfAttributesPerReferenceEntity $averageMaxNumberOfAttributesPerReferenceEntity,
        SqlLocalizableOnly $localizableOnly,
        SqlScopableOnly $scopableOnly,
        SqlScopableAndLocalizable $scopableAndLocalizable
    ) {
        $this->countReferenceEntities = $countReferenceEntities;
        $this->averageMaxNumberOfRecordsPerReferenceEntity = $averageMaxNumberOfRecordsPerReferenceEntity;
        $this->averageMaxNumberOfValuesPerRecord = $averageMaxNumberOfValuesPerRecord;
        $this->averageMaxNumberOfAttributesPerReferenceEntity = $averageMaxNumberOfAttributesPerReferenceEntity;
        $this->localizableOnly = $localizableOnly;
        $this->scopableOnly = $scopableOnly;
        $this->scopableAndLocalizable = $scopableAndLocalizable;
    }

    public function collect(): array
    {
        $averageMaxNumberOfRecordsPerReferenceEntity = $this->averageMaxNumberOfRecordsPerReferenceEntity->fetch();
        $averageMaxNumberOfAttributesPerReferenceEntity = $this->averageMaxNumberOfAttributesPerReferenceEntity->fetch();

        return [
            'nb_reference_entities' => $this->countReferenceEntities->fetch()->getVolume(),
            'max_number_of_records_per_reference_entity' => $averageMaxNumberOfRecordsPerReferenceEntity->getMaxVolume(),
            'average_number_of_records_per_reference_entity' => $averageMaxNumberOfRecordsPerReferenceEntity->getAverageVolume(),
            'average_number_of_values_per_records' => $this->averageMaxNumberOfValuesPerRecord->fetch()->getAverageVolume(),
            'max_number_of_attributes_per_reference_entity' => $averageMaxNumberOfAttributesPerReferenceEntity->getMaxVolume(),
            'average_number_of_attributes_per_reference_entity' => $averageMaxNumberOfAttributesPerReferenceEntity->getAverageVolume(),
            'average_percentage_localizable_only_attributes' => $this->localizableOnly->fetch()->getAverageVolume(),
            'average_percentage_scopable_only_attributes' => $this->scopableOnly->fetch()->getAverageVolume(),
            'average_percentage_scopable_and_localizable_attributes' => $this->scopableAndLocalizable->fetch()->getAverageVolume(),
        ];
    }
}
