<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;

/**
 * Metric filter related to flexible entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter extends NumberFilter
{
    /** @var MeasureConverter $converter */
    protected $converter;

    /**
     * @param TranslatorInterface $translator
     * @param MeasureConverter    $converter
     */
    public function __construct(TranslatorInterface $translator, MeasureConverter $converter)
    {
        parent::__construct($translator);

        $this->converter = $converter;
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
        $dataType = $this->getOption('data_type');

        list($formType, $formOptions) = parent::getRenderSettings();
        $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;

        return array($formType, $formOptions);
    }
}
