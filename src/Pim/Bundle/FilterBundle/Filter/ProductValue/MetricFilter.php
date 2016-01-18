<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Metric filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter extends OroNumberFilter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var MeasureManager
     */
    protected $measureManager;

    /**
     * @var string
     */
    protected $family;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param ProductFilterUtility $util
     * @param TranslatorInterface  $translator
     * @param MeasureManager       $measureManager
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        TranslatorInterface $translator,
        MeasureManager $measureManager
    ) {
        parent::__construct($factory, $util);

        $this->translator     = $translator;
        $this->measureManager = $measureManager;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params)
    {
        parent::init($name, $params);

        $this->family = $params['family'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return MetricFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);
        $ds->generateParameterName($this->getName());

        $data['data'] = $data['value'];
        unset($data['value']);
        unset($data['type']);

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data
        );

        return true;
    }

    /**
     * Overriden to validate metric unit
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data['type'] = isset($data['type']) ? $data['type'] : null;

        if (!is_array($data)
            || !array_key_exists('value', $data)
            || (!is_numeric($data['value']) && FilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }

        if (!is_array($data)
            || !array_key_exists('unit', $data)
            || (!is_string($data['unit']) && FilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['units'] = $this->measureManager->getUnitSymbolsForFamily($this->family);

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator($type)
    {
        $operatorTypes = array(
            NumberFilterType::TYPE_EQUAL         => '=',
            NumberFilterType::TYPE_GREATER_EQUAL => '>=',
            NumberFilterType::TYPE_GREATER_THAN  => '>',
            NumberFilterType::TYPE_LESS_EQUAL    => '<=',
            NumberFilterType::TYPE_LESS_THAN     => '<',
            FilterType::TYPE_EMPTY               => 'EMPTY'
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }
}
