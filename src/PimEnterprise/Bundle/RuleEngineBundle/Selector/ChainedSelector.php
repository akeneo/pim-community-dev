<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Selector;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Chained rule loader
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChainedSelector implements SelectorInterface
{
    /** @var SelectorInterface[] ordered loaders by priority */
    protected $selectors = [];

    /**
     * @param SelectorInterface $loader
     *
     * @return ChainedSelector
     */
    public function addSelector(SelectorInterface $loader)
    {
        $this->selectors[] = $loader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $instance)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function select(RuleInterface $rule)
    {
        foreach ($this->selectors as $loader) {
            if ($loader->supports($rule)) {
                $ruleSubjectSet = $loader->select($rule);
                $ruleSubjectSet->setCode($rule->getCode());

                return $ruleSubjectSet;
            }
        }

        throw new \LogicException(sprintf('No loader available for the rule "%s".', $rule->getCode()));
    }
}
