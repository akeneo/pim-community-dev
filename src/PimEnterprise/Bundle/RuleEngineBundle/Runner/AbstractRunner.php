<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Runner;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;

/**
 * Rule runner
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
abstract class AbstractRunner implements RunnerInterface
{
    /** @var LoaderInterface */
    protected $loader;

    /** @var SelectorInterface */
    protected $selector;

    /** @var ApplierInterface */
    protected $applier;

    /**
     * @param LoaderInterface   $loader
     * @param SelectorInterface $selector
     * @param ApplierInterface  $applier
     */
    public function __construct(LoaderInterface $loader, SelectorInterface $selector, ApplierInterface $applier)
    {
        $this->loader   = $loader;
        $this->selector = $selector;
        $this->applier  = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public function run(RuleInterface $rule)
    {
        $loadedRule = $this->loader->load($rule);
        $subjects = $this->selector->select($loadedRule);

        $this->applier->apply($loadedRule, $subjects);
    }
}
