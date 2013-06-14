<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\ORM\Query\Expr;

/**
 * Overriding of Choice filter to link an entity with another one having many to many join
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryFilter extends EntityFilter
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

        if ('IN' === $operator) {
            $expression = $this->getExpressionFactory()->in(
                $this->createFieldExpression($this->getOption('mapped_property'), $newAlias),
                $data['value'][0]
            );
        } elseif ('UNCLASSIFIED' === $operator) {
            // FIXME : Waiting for doctrine 2 fix -> http://www.doctrine-project.org/jira/browse/DDC-1858
            // For now we use a non-performing but working query

            // create subrequest with classified node
            $fieldRoot = $this->createFieldExpression('root', 'c');
            $exprAnd = $this->getExpressionFactory()->eq($fieldRoot, $data['value'][0]);
            $qb = clone $proxyQuery->getQueryBuilder();
            $qb->select('p.id')->distinct()
               ->from('PimProductBundle:Product', 'p')
               ->leftJoin('p.categories', 'c')
               ->andWhere($exprAnd);

            // get classified product ids
            $results = $qb->getQuery()->getArrayResult();
            $productIds = array();
            foreach ($results as $resId) {
                $productIds[] = $resId['id'];
            }

            $fieldProduct = $this->createFieldExpression('id', $alias);
            if (count($productIds) > 0) {
                $expression = $this->getExpressionFactory()->notIn($fieldProduct, $productIds);
            }
        } elseif ('CLASSIFIED' === $operator) {
            $expression = $this->getExpressionFactory()->eq(
                $this->createFieldExpression('root', $newAlias),
                $data['value'][0]
            );

            $this->applyFilterToClause($proxyQuery, $expression);
        } else {
            $expression = $this->getExpressionFactory()->notIn(
                $this->createFieldExpression($this->getOption('mapped_property'), $newAlias),
                $data['value'][0]
            );
        }

        if (isset($expression)) {
            $this->applyFilterToClause($proxyQuery, $expression);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator($type)
    {
        $type = (int) $type;

        $operatorTypes = array(
            CategoryFilterType::TYPE_CONTAINS     => 'IN',
            CategoryFilterType::TYPE_NOT_CONTAINS => 'NOT IN',
            CategoryFilterType::TYPE_CLASSIFIED   => 'CLASSIFIED',
            CategoryFilterType::TYPE_UNCLASSIFIED => 'UNCLASSIFIED'
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : 'IN';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return false;
        }

        if (!is_array($data['value'])) {
            $data['value'] = array($data['value']);
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array_merge(
            parent::getDefaultOptions(),
            array('form_type' => CategoryFilterType::NAME)
        );
    }
}
