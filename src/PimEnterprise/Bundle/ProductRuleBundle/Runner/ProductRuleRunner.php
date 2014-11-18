<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Runner;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\AbstractRunner;

/**
 * Product rule runner
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleRunner extends AbstractRunner
{
    /**
     * {@inheritdoc}
     */
    public function run(RuleInterface $rule, array $context = [])
    {
        $loadedRule = $this->loader->load($rule);

        // TODO option resolver
        if (isset($context['selected_products'])) {
            $loadedRule->addCondition(
                [
                    'field' => 'id',
                    'operator' => 'IN',
                    'value' => $context['selected_products']
                ]
            );
        }

        $subjects = $this->selector->select($loadedRule);
        if (!empty($subjects)) {
            $this->applier->apply($loadedRule, $subjects);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }
}
