<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

trait FlexibleFilterTrait
{
    /** @var FlexibleManagerRegistry */
    protected $registry;

    /** @var string */
    protected $flexibleEntityName;

    public function __construct(FlexibleManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Gets flexible manager
     *
     * @return FlexibleManager
     * @throws \LogicException
     */
    protected function getFlexibleManager()
    {
        $flexibleEntityName = $this->flexibleEntityName;
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }
        return $this->registry->getManager($flexibleEntityName);
    }

    /**
     * Setter for flexible entity name
     *
     * @param string $flexibleEntityName
     */
    protected function setFlexibleEntityName($flexibleEntityName)
    {
        $this->flexibleEntityName = $flexibleEntityName;
    }
}
