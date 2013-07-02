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
     * Setter for class name used for filtering
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
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
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
     * {@inheritdoc}
     */
    protected function applyFlexibleFilter(ProxyQueryInterface $proxyQuery, $field, $value, $operator)
    {
        $attribute = $this->getAttribute($field);
        $qb = $proxyQuery->getQueryBuilder();

        // inner join to value
        $joinAlias = 'filter'.$field;
        $condition = $qb->prepareAttributeJoinCondition($attribute, $joinAlias);
        $qb->innerJoin($qb->getRootAlias() .'.'. $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

        // then join to linked entity with filter on id
        $joinAliasEntity = 'filterentity'.$field;
        $backendField = sprintf('%s.id', $joinAliasEntity);
        $condition = $qb->prepareCriteriaCondition($backendField, $operator, $value);
        $qb->innerJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasEntity, 'WITH', $condition);

        // filter is active since it's applied to the flexible repository
        $this->active = true;
    }
}
