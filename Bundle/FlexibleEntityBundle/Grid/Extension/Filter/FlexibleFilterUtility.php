<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Extension\Configuration;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

class FlexibleFilterUtility
{
    const FEN_KEY         = 'flexible_entity_name';
    const PARENT_TYPE_KEY = 'parent_type';

    public static $paramMap = [
        self::PARENT_TYPE_KEY => Configuration::TYPE_KEY
    ];

    /** @var FlexibleManagerRegistry */
    protected $fmr;

    public function __construct(FlexibleManagerRegistry $fmr)
    {
        $this->fmr = $fmr;
    }

    /**
     * Gets flexible manager
     *
     * @param string $flexibleEntityName
     *
     * @throws \LogicException
     * @return FlexibleManager
     */
    public function getFlexibleManager($flexibleEntityName)
    {
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }

        return $this->fmr->getManager($flexibleEntityName);
    }

    /**
     * Applies filter to query by flexible attribute
     *
     * @param QueryBuilder $qb
     * @param string       $flexibleEntityName
     * @param string       $field
     * @param mixed        $value
     * @param string       $operator
     */
    public function applyFlexibleFilter($qb, $flexibleEntityName, $field, $value, $operator)
    {
        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->getFlexibleManager($flexibleEntityName)
            ->getFlexibleRepository();

        $entityRepository->applyFilterByAttribute($qb, $field, $value, $operator);
    }
}
