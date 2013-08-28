<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\BooleanFilter;

/**
 * Overriding of boolean filter to filter by the product completeness
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
            case BooleanFilterType::TYPE_NO:
                $expression = $expressionFactory->neq($fieldExpression .'.ratio', '100');
                break;
            case BooleanFilterType::TYPE_YES:
            default:
                $expression = $expressionFactory->eq($fieldExpression .'.ratio', '100');
                break;
        }

        $queryBuilder->andWhere($expression);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => BooleanFilterType::NAME
        );
    }
}
