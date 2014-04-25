<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

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
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var MeasureConverter $converter
     */
    protected $converter;

    /**
     * @var MeasureManager $measureManager
     */
    protected $measureManager;

    /**
     * @var string $family
     */
    protected $family;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param ProductFilterUtility $util
     * @param TranslatorInterface  $translator
     * @param MeasureManager       $measureManager
     * @param MeasureConverter     $converter
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        TranslatorInterface $translator,
        MeasureManager $measureManager,
        MeasureConverter $converter
    ) {
        parent::__construct($factory, $util);

        $this->translator     = $translator;
        $this->measureManager = $measureManager;
        $this->converter      = $converter;
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

        // Convert value to base unit
        if ('EMPTY' !== $operator) {
            $this->converter->setFamily($this->family);
            $baseValue = $this->converter->convertBaseToStandard($data['unit'], $data['value']);
        } else {
            $baseValue = null;
        }

        $this->util->applyFilterByAttribute(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $baseValue,
            $operator
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
            || (!is_numeric($data['value']) && NumberFilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }

        if (!is_array($data)
            || !array_key_exists('unit', $data)
            || (!is_string($data['unit']) && NumberFilterType::TYPE_EMPTY !== $data['type'])) {
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
            NumberFilterType::TYPE_EMPTY         => 'EMPTY'
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }
}
