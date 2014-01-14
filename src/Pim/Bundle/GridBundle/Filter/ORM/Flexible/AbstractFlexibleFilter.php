<?php

namespace Pim\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFlexibleFilter implements FilterInterface
{
    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleRegistry;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var string
     */
    protected $parentFilterClass;

    /**
     * @var AbstractFilter
     */
    protected $parentFilter;

    /**
     * @param FlexibleManagerRegistry $flexibleRegistry
     * @param FilterInterface         $parentFilter
     *
     * @throws \InvalidArgumentException If $parentFilter has invalid type
     */
    public function __construct(FlexibleManagerRegistry $flexibleRegistry, FilterInterface $parentFilter)
    {
        $this->flexibleRegistry = $flexibleRegistry;
        $this->parentFilter = $parentFilter;
        if ($this->parentFilterClass && !$this->parentFilter instanceof $this->parentFilterClass) {
            throw new \InvalidArgumentException('Parent filter must be an instance of ' . $this->parentFilterClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        $this->parentFilter->initialize($name, $options);
        $this->loadFlexibleManager();
    }

    /**
     * Gets flexible manager
     *
     * @return FlexibleManager
     * @throws \LogicException
     */
    protected function getFlexibleManager()
    {
        $this->loadFlexibleManager();

        return $this->flexibleManager;
    }

    /**
     * Load flexible manager name
     *
     * @throws \LogicException
     */
    protected function loadFlexibleManager()
    {
        if (!$this->flexibleManager) {
            $flexibleEntityName = $this->getOption('flexible_name');
            if (!$flexibleEntityName) {
                throw new \LogicException('Flexible entity filter must have flexible entity name.');
            }
            $this->flexibleManager = $this->flexibleRegistry->getManager($flexibleEntityName);
        }
    }

    /**
     * Apply filter using flexible repository
     *
     * @param ProxyQueryInterface $proxyQuery the query
     * @param string              $field      the field
     * @param string              $value      the value
     * @param string              $operator   the operator
     */
    protected function applyFlexibleFilter(ProxyQueryInterface $proxyQuery, $field, $value, $operator)
    {
        /** @var $proxyQuery ProxyQuery */
        $queryBuilder = $proxyQuery->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->getFlexibleManager()->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $field, $value, $operator);

        // filter is active since it's applied to the flexible repository
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return $this->parentFilter->getDefaultOptions();
    }

    /**
     * Returns the main widget used to render the filter
     *
     * @return array
     */
    public function getRenderSettings()
    {
        return $this->parentFilter->getRenderSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
        list($alias, $field) = $this->association($queryBuilder);
        $this->filter($queryBuilder, $alias, $field, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function association(ProxyQueryInterface $queryBuilder)
    {
        // TODO We can skip call entityJoin because flexible attributes don't have association mappings
        $alias = $queryBuilder->entityJoin($this->getParentAssociationMappings());

        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['entityAlias'])) {
            $alias = $fieldMapping['entityAlias'];
        }

        return array($alias, $this->getFieldName());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->parentFilter->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->parentFilter->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->parentFilter->setLabel($label);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return $this->parentFilter->getOption($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->parentFilter->setOption($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->parentFilter->getFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentAssociationMappings()
    {
        return $this->parentFilter->getParentAssociationMappings();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        return $this->parentFilter->getFieldMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMapping()
    {
        return $this->parentFilter->getAssociationMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldOptions()
    {
        return $this->parentFilter->getFieldOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType()
    {
        return $this->parentFilter->getFieldType();
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable()
    {
        return $this->parentFilter->isNullable();
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
