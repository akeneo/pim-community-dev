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
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $context = $this->resolveContext($context);
        $loadedRule = $this->loadRule($rule, $context);

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

    /**
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['selected_products' => []]);
        $resolver->setAllowedTypes(['selected_products' => 'array']);
        $context = $resolver->resolve($context);

        return $context;
    }

    /**
     * @param RuleInterface $rule
     * @param array         $context
     *
     * @return LoadedRuleInterface
     */
    protected function loadRule(RuleInterface $rule, array $context)
    {
        $loadedRule = $this->loader->load($rule);
        if (!empty($context['selected_products'])) {
            $loadedRule->addCondition(
                [
                    'field' => 'id',
                    'operator' => 'IN',
                    'value' => $context['selected_products']
                ]
            );
        }

        return $loadedRule;
    }
}
