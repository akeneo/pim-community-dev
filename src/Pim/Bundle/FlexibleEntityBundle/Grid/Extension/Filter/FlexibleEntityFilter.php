<?php

namespace Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Filter\EntityFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class FlexibleEntityFilter extends EntityFilter
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
        $fm            = $this->util->getFlexibleManager($this->get(FilterUtility::FEN_KEY));
        $valueName     = $fm->getFlexibleValueName();
        $valueMetadata = $fm->getStorageManager()
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
