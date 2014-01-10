<?php

namespace Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

use Pim\Bundle\FilterBundle\Form\Type\Filter\PriceFilterType;

/**
 * Price filter related to flexible entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexiblePriceFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => PriceFilterType::NAME
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
        $currency = $data['currency'];

        $newAlias = 'valuePrices';

        // Apply clause on currency code
        $paramCurrency = $this->getNewParameterName($proxyQuery);
        $exprEq = $this->createCompareFieldExpression('currency', $newAlias, '=', $paramCurrency);
        $proxyQuery->setParameter($paramCurrency, $currency);

        // Apply clause on operator and value
        $paramValue = $this->getNewParameterName($proxyQuery);
        $exprCmp = $this->createCompareFieldExpression('data', $newAlias, $operator, $paramValue);
        $proxyQuery->setParameter($paramValue, $data['value']);

        $expression = $this->getExpressionFactory()->andX($exprEq, $exprCmp);
        $this->applyFilterToClause($proxyQuery, $expression);
    }

    /**
     * Overriden to validate currency option
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data = parent::parseData($data);

        if (!is_array($data) || !array_key_exists('currency', $data) || !is_string($data['currency'])) {
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

        switch ($dataType) {
            case FieldDescriptionInterface::TYPE_INTEGER:
                $formOptions['data_type'] = NumberFilterType::DATA_INTEGER;
                break;
            case FieldDescriptionInterface::TYPE_DECIMAL:
            case FieldDescriptionInterface::TYPE_PERCENT:
            default:
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
                break;
        }

        return array($formType, $formOptions);
    }
}
