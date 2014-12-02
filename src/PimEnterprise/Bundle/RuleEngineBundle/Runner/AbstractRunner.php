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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;

/**
 * Rule runner
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
abstract class AbstractRunner implements RunnerInterface
{
    /** @var BuilderInterface */
    protected $builder;

    /** @var SelectorInterface */
    protected $selector;

    /** @var ApplierInterface */
    protected $applier;

    /**
     * @param BuilderInterface  $builder
     * @param SelectorInterface $selector
     * @param ApplierInterface  $applier
     */
    public function __construct(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier
    ) {
        $this->builder   = $builder;
        $this->selector = $selector;
        $this->applier  = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public function run(RuleDefinitionInterface $definition, array $options = [])
    {
        $definition = $this->builder->build($definition);
        $subjects = $this->selector->select($definition);
        if (!empty($subjects)) {
            $this->applier->apply($definition, $subjects);
        }
    }
}
