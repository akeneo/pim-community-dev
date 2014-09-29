<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Loader;

use PimEnterprise\Bundle\RuleEngineBundle\Entity\RuleInstanceInterface;

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
