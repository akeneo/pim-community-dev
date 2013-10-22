<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Extension\Orm as Base;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

class StringFilter extends Base\StringFilter
{
    use FlexibleFilterTrait;

    /** @var FlexibleManagerRegistry */
    protected $registry;

    public function __construct(FormFactoryInterface $factory, FlexibleManagerRegistry $registry)
    {
        parent::__construct($factory);
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if ($data) {
            $operator = $this->getOperator($data['type']);

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $this->getFlexibleManager($this->registry, $this->get('flexible_entity_name'))
                ->getFlexibleRepository();
            $entityRepository->applyFilterByAttribute($qb, $this->get('data_name'), $data['value'], $operator);

            return true;
        }

        return false;
    }
}
