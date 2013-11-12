<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Extension\Orm\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class FlexibleBooleanFilter extends BooleanFilter
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
        if (!$data) {
            return false;
        }

        $field = $this->get(self::DATA_NAME_KEY);
        $value = ($data['value'] == BooleanFilterType::TYPE_YES) ? 1 : 0;

        $this->util->applyFlexibleFilter($qb, $this->get(FlexibleFilterUtility::FEN_KEY), $field, $value, '=');

        return true;
    }
}
