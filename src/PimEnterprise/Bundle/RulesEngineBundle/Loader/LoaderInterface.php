<?php

namespace Pim\Bundle\RulesEngineBundle\Loader;

use PIm\Bundle\RuleInterface\Model\RuleInterface;

/**
 * Transform an rule instance (an entity) to a business rule
 */
interface LoaderInterface
{
    /**
     * @param RuleInstanceInterface $instance
     *
     * @return RuleInterface
     */
    public function load(RuleInstanceInterface $instance);

    public function supports(RuleInstanceInterface $instance)
    {
        return true;
    }
}
