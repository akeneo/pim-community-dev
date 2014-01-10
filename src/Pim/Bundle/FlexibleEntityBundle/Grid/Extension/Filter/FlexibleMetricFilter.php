<?php

namespace Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
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
     * @param FilterUtility        $util
     * @param TranslatorInterface  $translator
     * @param MeasureManager       $measureManager
     * @param MeasureConverter     $converter
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
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
        $parameterName = $ds->generateParameterName($this->getName());

        // Convert value to base unit
        $this->converter->setFamily($this->family);
        $baseValue = $this->converter->convertBaseToStandard($data['unit'], $data['value']);

        $this->applyFilterToClause(
            $ds,
            $ds->expr()->comparison($this->get(FilterUtility::DATA_NAME_KEY), $operator, $parameterName, true)
        );

        $ds->setParameter($parameterName, $baseValue);

        return true;
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
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['units'] = $this->measureManager->getUnitSymbolsForFamily($this->family);

        return $metadata;
    }
}
