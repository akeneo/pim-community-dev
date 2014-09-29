<?php

namespace Pim\Bundle\RulesEngineBundle\Loader;

use Pim\Bundle\RulesEngineBundle\Entity\LoaderInterface;
use Pim\Bundle\RulesEngineBundle\Entity\RuleInstanceInterface;

class ChainedLoader implements LoaderInterface
{
    /** LoaderInterface[] ordered loaders with priority */
    protected $loaders;

    public function registerLoader(LoaderInterface $loader)
    {
        $this->loaders[]= $loader;
    }

    public function supports(RuleInstanceInterface $instance)
    {
        return true;
    }

    public function load(RuleInstanceInterface $instance)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($loader)) {
                return $loader->load($instance);
            }
        }

        throw new \LogicException('No loader available');
    }
}
