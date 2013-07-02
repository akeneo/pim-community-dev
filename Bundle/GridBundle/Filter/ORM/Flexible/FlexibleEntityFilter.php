<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

/**
 * Create flexible filter for entity values linked to attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityFilter extends FlexibleOptionsFilter
{

    /**
     * Setter for class name
     *
     * @param string $className
     *
     * @return \Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleEntityFilter
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueOptions()
    {
        if (null === $this->valueOptions) {
            $entityManager    = $this->getFlexibleManager()->getStorageManager();
            $entityRepository = $entityManager->getRepository($this->className);
            $entities         = $entityRepository->findAll();

            $this->valueOptions = array();
            foreach ($entities as $entity) {
                $this->valueOptions[$entity->getId()] = $entity->__toString();
            }
        }

        return $this->valueOptions;
    }

    /**
     * Get the attribute linked to the flexible entity filter
     * @return unknown
     */
    protected function getAttribute($attributeCode)
    {
        $attribute = $this->getFlexibleManager()
                          ->getAttributeRepository()
                          ->findOneBy(array('code' => $attributeCode));

        if (!$attribute) {
            throw new \LogicException('Impossible to find attribute');
        }

        return $attribute;
    }

    /**
     * Apply filter using flexible repository
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string $field
     * @param string $value
     * @param string $operator
     */
    protected function applyFlexibleFilter(ProxyQueryInterface $proxyQuery, $field, $value, $operator)
    {
        $attribute = $this->getAttribute($field);

        $qb = $proxyQuery->getQueryBuilder();

        $joinAlias = 'filter'.$field;

        // inner join to value
        $condition = $qb->prepareAttributeJoinCondition($attribute, $joinAlias);
        $qb->innerJoin($qb->getRootAlias() .'.'. $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

        // then join to color with filter on option id
        $joinAliasColor = 'filterentity'.$field;
        $backendField = sprintf('%s.%s', $joinAliasColor, 'id');
        $condition = $qb->prepareCriteriaCondition($backendField, $operator, $value);
        $qb->innerJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasColor, 'WITH', $condition);

        // filter is active since it's applied to the flexible repository
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator($type)
    {
        $type = (int) $type;

        $operatorTypes = array(
                1 => 'IN',
                2 => 'NOT IN'
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
}
