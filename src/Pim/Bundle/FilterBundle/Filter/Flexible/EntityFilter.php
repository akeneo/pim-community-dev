<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FilterBundle\Filter\EntityFilter as OroEntityFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityFilter extends OroEntityFilter
{
    const BACKEND_TYPE_KEY = 'backend_type';

    public function init($name, array $params)
    {
        $params['class'] = $this->getClassName();
        parent::init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);

        $this->util->applyFlexibleFilter(
            $ds,
            $this->get(FilterUtility::FEN_KEY),
            $this->get(FilterUtility::DATA_NAME_KEY),
            $this->extractIds($data['value']),
            $operator
        );

        return true;
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
        /** @var FlexibleManager $fm */
        $flexibleManager = $this->util->getFlexibleManager($this->get(FilterUtility::FEN_KEY));
        $valueName       = $flexibleManager->getFlexibleValueName();
        $valueMetadata   = $flexibleManager->getStorageManager()
            ->getMetadataFactory()
            ->getMetadataFor($valueName);

        return $valueMetadata->getAssociationTargetClass($this->get(self::BACKEND_TYPE_KEY));
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
