<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Presenter\NumberPresenter;
use Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository;
use Psr\Log\LoggerInterface;

/**
 * Metric presenter, able to render metric data readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricPresenter extends NumberPresenter
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    /** @var BaseCachedObjectRepository */
    private $baseCachedObjectRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        NumberFactory $numberFactory,
        array $attributeTypes,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        BaseCachedObjectRepository $baseCachedObjectRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($numberFactory, $attributeTypes);

        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->baseCachedObjectRepository = $baseCachedObjectRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (isset($options['versioned_attribute'])) {
            $value = $this->getStructuredMetric($value, $options['versioned_attribute']);
        }

        $unitLabel = '';

        if (
            isset($options['attribute'])
            && isset($options['locale'])
            && is_array($value)
            && array_key_exists('unit', $value)
        ) {
            if (!empty($value['unit'])) {
                try {
                    $measurementFamilyCode = $this->baseCachedObjectRepository->findOneByIdentifier($options['attribute'])
                        ->getMetricFamily();
                    $measurementFamily = $this->measurementFamilyRepository
                        ->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));
                    $unitLabel = $measurementFamily->getUnitLabel(
                        UnitCode::fromString($value['unit']),
                        LocaleIdentifier::fromCode($options['locale'])
                    );
                } catch (\Exception $e) {
                    $this->logger->warning('An error occurred while trying to fetch the measurement family of a metric value to present it.', [
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTrace(),
                    ]);
                }
            }
        } else {
            $this->logger->warning('Expected to have an attribute code given in the options of the presenter, none given.', [
                'options' => [
                    'attribute' => $options['attribute'] ?? 'undefined',
                    'unit' => $value['unit'] ?? 'undefined',
                    'locale' => $options['locale'] ?? 'undefined',
                ],
            ]);
        }

        $amount = isset($value['amount']) ? parent::present($value['amount'], $options) : null;

        return join(' ', array_filter([$amount, $unitLabel], fn ($value) => null !== $value && '' !== $value));
    }

    /**
     * Get the metric with format data and unit from the versioned attribute.
     * The versionedAttribute can be "weight" (then the value is the data, without the unit), or "weight-unit" (then
     * the value is the unit, without any data).
     *
     * @param string $value
     * @param string $versionedAttribute
     *
     * @return array
     */
    protected function getStructuredMetric($value, $versionedAttribute)
    {
        $parts = preg_split('/-/', $versionedAttribute);
        $unit = end($parts);

        return ('unit' === $unit) ? ['amount' => null, 'unit' => $value] : ['amount' => $value, 'unit' => null];
    }
}
