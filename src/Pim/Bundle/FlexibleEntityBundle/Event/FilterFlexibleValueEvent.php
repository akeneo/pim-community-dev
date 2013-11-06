<?php

namespace Pim\Bundle\FlexibleEntityBundle\Event;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Filter event allows to know the create flexible value
 */
class FilterFlexibleValueEvent extends AbstractFilterEvent
{
    /**
     * Flexible value
     * @var FlexibleValueInterface
     */
    protected $value;

    /**
     * Constructor
     *
     * @param FlexibleManager        $manager the manager
     * @param FlexibleValueInterface $value   the value
     */
    public function __construct(FlexibleManager $manager, FlexibleValueInterface $value)
    {
        parent::__construct($manager);
        $this->value = $value;
    }

    /**
     * @return FlexibleValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}
