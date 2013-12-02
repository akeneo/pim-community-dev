<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Extension\Orm\EntityFilter;

class FlexibleEntityFilter extends EntityFilter
{
    const BACKEND_TYPE_KEY = 'backend_type';

    /** @var FlexibleFilterUtility */
    protected $util;

    public function __construct(FormFactoryInterface $factory, FlexibleFilterUtility $util)
    {
        parent::__construct($factory);
        $this->util = $util;
        $this->paramMap = FlexibleFilterUtility::$paramMap;
    }

    public function init($name, array $params)
    {
        $params['class'] = $this->getClassName();
        parent::init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if ($data) {
            $operator = $this->getOperator($data['type']);

            $this->util->applyFlexibleFilter(
                $qb,
                $this->get(FlexibleFilterUtility::FEN_KEY),
                $this->get(self::DATA_NAME_KEY),
                $this->extractIds($data['value']),
                $operator
            );

            return true;
        }

        return false;
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
        $fm            = $this->util->getFlexibleManager($this->get(FlexibleFilterUtility::FEN_KEY));
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
