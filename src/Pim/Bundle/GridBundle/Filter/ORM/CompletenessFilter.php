<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\BooleanFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CompletenessFilterType;

/**
 * Overriding of boolean filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends BooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $fieldExpression = $this->createFieldExpression($field, $alias);
        $expressionFactory = $this->getExpressionFactory();

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $expression = $expressionFactory->eq($fieldExpression, '100');
                break;
            case BooleanFilterType::TYPE_NO:
                $expression = $expressionFactory->neq($fieldExpression, '100');
                break;
            default:
                break;
        }

        $this->applyFilterToClause($queryBuilder, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => CompletenessFilterType::NAME
        );
    }
}
