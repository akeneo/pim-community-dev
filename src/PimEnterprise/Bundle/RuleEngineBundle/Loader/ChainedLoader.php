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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInstanceInterface;

/**
 * Chained rule loader
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChainedLoader implements LoaderInterface
{
    /** @var LoaderInterface[] ordered loaders by priority */
    protected $loaders;

    /**
     * @param LoaderInterface $loader
     *
     * @return ChainedLoader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInstanceInterface $instance)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RuleInstanceInterface $instance)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($instance)) {
                return $loader->load($instance);
            }
        }

        throw new \LogicException(sprintf('No loader available for the instance "%s".', $instance->getCode()));
    }
}
