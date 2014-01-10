<?php

namespace Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;

/**
 * Metric filter related to flexible entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleMetricFilter extends NumberFilter
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var MeasureConverter $converter
     */
    protected $converter;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param TranslatorInterface  $translator
     * @param MeasureConverter     $converter
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        TranslatorInterface $translator,
        MeasureConverter $converter
    ) {
        parent::__construct($factory, $util);

        $this->converter  = $converter;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        $this->name = $name;
        $this->setOptions($options);
        $this->family = $options['field_options']['family'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => MetricFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);

        // Convert value to base unit
        $this->converter->setFamily($this->family);
        $baseValue = $this->converter->convertBaseToStandard($data['unit'], $data['value']);

        // Apply clause
        $paramValue = $this->getNewParameterName($proxyQuery);
        $exprCmp = $this->createCompareFieldExpression('baseData', 'valueMetrics', $operator, $paramValue);
        $this->applyFilterToClause($proxyQuery, $exprCmp);
        $proxyQuery->setParameter($paramValue, $baseValue);
    }

    /**
     * Overriden to validate metric unit
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data = parent::parseData($data);

        if (!is_array($data) || !array_key_exists('unit', $data) || !is_string($data['unit'])) {
            return false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        list($formType, $formOptions) = parent::getRenderSettings();
        $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;

        return array($formType, $formOptions);
    }
}
