<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;

/**
 * Overriding of Choice filter to link an entity with another one having many to many join
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EntityFilter extends ChoiceFilter
{

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

        $associations = array($this->getOption('field_mapping'));
        $newAlias = $proxyQuery->entityJoin($associations);

        if ('IN' == $operator) {
            $expression = $this->getExpressionFactory()->in(
                $this->createFieldExpression($this->getOption('mapped_property'), $newAlias),
                $data['value']
            );
        } else {
            $expression = $this->getExpressionFactory()->notIn(
                $this->createFieldExpression($this->getOption('mapped_property'), $newAlias),
                $data['value']
            );
        }

        $this->applyFilterToClause($proxyQuery, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array_merge(
            parent::getDefaultOptions(),
            array('mapped_property' => 'id')
        );
    }
}
