<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Presenter\NumberPresenter;
use Akeneo\Tool\Component\Localization\TranslatorProxy;

/**
 * Metric presenter, able to render metric data readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricPresenter extends NumberPresenter
{
    /** @var TranslatorProxy */
    protected $translatorProxy;

    /**
     * @param NumberFactory   $numberFactory
     * @param array           $attributeTypes
     * @param TranslatorProxy $translatorProxy
     */
    public function __construct(
        NumberFactory $numberFactory,
        array $attributeTypes,
        TranslatorProxy $translatorProxy
    ) {
        parent::__construct($numberFactory, $attributeTypes);

        $this->translatorProxy = $translatorProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (isset($options['versioned_attribute'])) {
            $value = $this->getStructuredMetric($value, $options['versioned_attribute']);
        }

        $amount = isset($value['amount']) ? parent::present($value['amount'], $options) : null;
        $unit = isset($value['unit']) && $value['unit'] !== ''
            ? $this->translatorProxy->trans(sprintf('pim_measure.units.%s', $value['unit'])
            ) : null;

        return join(' ', array_filter([ $amount, $unit ], function ($value) {
            return $value !== null;
        }));
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
