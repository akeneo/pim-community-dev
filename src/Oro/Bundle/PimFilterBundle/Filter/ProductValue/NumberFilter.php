<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Number filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends OroNumberFilter
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface         $factory
     * @param ProductFilterUtility         $util
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($factory, $util);

        $this->attributeRepository = $attributeRepository;
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

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data['value']
        );

        return true;
    }

    /**
     * Get operator string
     *
     * @param int $type
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getOperator($type)
    {
        $operatorTypes = [
            NumberFilterType::TYPE_EQUAL         => Operators::EQUALS,
            NumberFilterType::TYPE_GREATER_EQUAL => Operators::GREATER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_GREATER_THAN  => Operators::GREATER_THAN,
            NumberFilterType::TYPE_LESS_EQUAL    => Operators::LOWER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_LESS_THAN     => Operators::LOWER_THAN,
            FilterType::TYPE_EMPTY               => Operators::IS_EMPTY,
            FilterType::TYPE_NOT_EMPTY           => Operators::IS_NOT_EMPTY,
        ];

        if (!isset($operatorTypes[$type])) {
            throw new \InvalidArgumentException(sprintf('Operator "%s" is undefined', $type));
        }

        return $operatorTypes[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !array_key_exists('type', $data)
            || (
                !is_numeric($data['value']) &&
                !in_array($data['type'], [FilterType::TYPE_EMPTY, FilterType::TYPE_NOT_EMPTY])
            )) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $attribute = $this->getAttribute();
        $metadata = parent::getMetadata();

        if (true === $attribute->isDecimalsAllowed()) {
            $metadata['formatterOptions']['decimals'] = 2;
            $metadata['formatterOptions']['grouping'] = true;
        }

        return $metadata;
    }

    /**
     * Load the attribute for this filter
     *
     * @throws \LogicException
     */
    protected function getAttribute()
    {
        $fieldName = $this->get(ProductFilterUtility::DATA_NAME_KEY);
        $attribute = $this->attributeRepository->findOneByCode($fieldName);

        if (!$attribute) {
            throw new \LogicException(sprintf('There is no attribute with code %s.', $fieldName));
        }

        return $attribute;
    }
}
