<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Factory\NumberFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Metric presenter, able to render metric data readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricPresenter extends NumberPresenter
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param NumberFactory       $numberFactory
     * @param array               $attributeTypes
     * @param TranslatorInterface $translator
     */
    public function __construct(NumberFactory $numberFactory, array $attributeTypes, TranslatorInterface $translator)
    {
        parent::__construct($numberFactory, $attributeTypes);

        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (isset($options['versioned_attribute'])) {
            $value = $this->getStructuredMetric($value, $options['versioned_attribute']);
        }

        $amount = isset($value['data']) ? parent::present($value['data'], $options) : null;
        $unit = isset($value['unit']) ? $this->translator->trans($value['unit']) : null;

        return trim(sprintf('%s %s', $amount, $unit));
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

        return ('unit' === $unit) ? ['data' => null, 'unit' => $value] : ['data' => $value, 'unit' => null];
    }
}
