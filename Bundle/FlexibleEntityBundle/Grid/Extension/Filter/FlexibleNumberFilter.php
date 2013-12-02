<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Extension\Orm\NumberFilter;

class FlexibleNumberFilter extends NumberFilter
{
    /** @var FlexibleFilterUtility */
    protected $util;

    public function __construct(FormFactoryInterface $factory, FlexibleFilterUtility $util)
    {
        parent::__construct($factory);
        $this->util = $util;
        $this->paramMap = FlexibleFilterUtility::$paramMap;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if ($data) {
            $operator = $this->getOperator($data['type']);

            $fen = $this->get(FlexibleFilterUtility::FEN_KEY);
            $this->util->applyFlexibleFilter($qb, $fen, $this->get(self::DATA_NAME_KEY), $data['value'], $operator);

            return true;
        }

        return false;
    }
}
