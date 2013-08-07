<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CurrencyFilterType;

/**
 * Currency filter related to flexible entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => CurrencyFilterType::NAME
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

        $newAlias = 'ValuePrices';

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
        list($formType, $formOptions) = parent::getRenderSettings();

        $dataType = $this->getOption('data_type', FieldDescriptionInterface::TYPE_DECIMAL);
        switch ($dataType) {
            case FieldDescriptionInterface::TYPE_DECIMAL:
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
                break;
            case FieldDescriptionInterface::TYPE_INTEGER:
            default:
                $formOptions['data_type'] = NumberFilterType::DATA_INTEGER;
        }

        return array($formType, $formOptions);
    }
}
