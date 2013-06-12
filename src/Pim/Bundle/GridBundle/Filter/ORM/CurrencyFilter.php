<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;


use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CurrencyFilterType;

/**
 * Currency filter for products
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
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);
        $currency = $data['currency'];

        $newAlias = 'ValuePrices';

        // Apply clause on currency code
        $paramCurrency = $this->getNewParameterName($queryBuilder);
        $exprEq = $this->createCompareFieldExpression('currency', $newAlias, '=', $paramCurrency);
        $queryBuilder->setParameter($paramCurrency, $currency);

        // Apply clause on operator and value
        $paramValue = $this->getNewParameterName($queryBuilder);
        $exprCmp = $this->createCompareFieldExpression('data', $newAlias, $operator, $paramValue);
        $queryBuilder->setParameter($paramValue, $data['value']);

        $expression = $this->getExpressionFactory()->andX($exprEq, $exprCmp);
        $this->applyFilterToClause($queryBuilder, $expression);
    }

    /**
     * Overriden to validate currency option
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data = parent::parseData($data);

        if (!is_array($data) || !array_key_exists('currency', $data)) {
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
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
        }

        return array($formType, $formOptions);
    }
}
