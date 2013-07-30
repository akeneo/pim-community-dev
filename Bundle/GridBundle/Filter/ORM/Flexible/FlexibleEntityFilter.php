<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

/**
 * Create flexible filter for entity values linked to attribute
 */
class FlexibleEntityFilter extends AbstractFlexibleFilter
{
    /**
     * The attribute defining the entity linked
     *
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected $attribute;

    /**
     * FQCN of the linked entity
     *
     * @var string
     */
    protected $className;

    /**
     * @var \Oro\Bundle\GridBundle\Filter\ORM\EntityFilter
     */
    protected $parentFilter;

    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\EntityFilter';

    /**
     * @var array
     */
    protected $valueOptions;

    /**
     * Override initialize method to instanciate attribute and FQCN of the linked entity
     *
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        parent::initialize($name, $options);

        $this->setOption('backend_type', $this->getAttribute()->getBackendType());
        $this->setOption('class', $this->getClassName());
    }

    /**
     * {@inheritdoc}
     */
    public function filter(\Sonata\AdminBundle\Datagrid\ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        $data = $this->parentFilter->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->parentFilter->getOperator($data['type']);

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $this->extractIds($data['value']), $operator);
    }

    /**
     * Get the attribute linked to the flexible entity filter
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected function getAttribute()
    {
        $attribute = $this->getFlexibleManager()
                          ->getAttributeRepository()
                          ->findOneBy(array('code' => $this->getOption('field_name')));

        if (!$attribute) {
            throw new \LogicException('Impossible to find attribute');
        }

        return $attribute;
    }

    /**
     * Get the class name of the entity linked
     *
     * @return string
     *
     * @throws \LogicException
     */
    protected function getClassName()
    {
        $valueName = $this->flexibleManager->getFlexibleValueName();
        $valueMetadata = $this->flexibleManager->getStorageManager()
                                               ->getMetadataFactory()
                                               ->getMetadataFor($valueName);

        return $valueMetadata->getAssociationTargetClass($this->getOption('backend_type'));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilter(ProxyQueryInterface $proxyQuery, $field, $value, $operator)
    {
        $attribute = $this->getAttribute($field);
        /** @var $qb FlexibleQueryBuilder */
        $qb = $proxyQuery->getQueryBuilder();

        // inner join to value
        $joinAlias = 'filter'.$field;
        $condition = $qb->prepareAttributeJoinCondition($attribute, $joinAlias);
        $rootAlias = $qb->getRootAliases();
        $qb->innerJoin($rootAlias[0] .'.'. $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

        // then join to linked entity with filter on id
        $joinAliasEntity = 'filterentity'.$field;
        $backendField = sprintf('%s.id', $joinAliasEntity);
        $condition = $qb->prepareCriteriaCondition($backendField, $operator, $value);
        $qb->innerJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasEntity, 'WITH', $condition);

        // filter is active since it's applied to the flexible repository
        $this->active = true;
    }

    /**
     * Extract collection ids
     *
     * @param ArrayCollection $entities
     *
     * @return array
     */
    public function extractIds($entities)
    {
        $entityIds = array();
        foreach ($entities as $entity) {
            $entityIds[] = $entity->getId();
        }

        return $entityIds;
    }
}
