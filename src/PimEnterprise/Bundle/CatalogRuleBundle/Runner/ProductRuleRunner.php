<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Runner;

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
    public function run(RuleInterface $rule, array $options = [])
    {
        $options = $this->resolveOptions($options);
        $loadedRule = $this->loadRule($rule, $options);

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
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['selected_products' => []]);
        $resolver->setAllowedTypes(['selected_products' => 'array']);
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @param RuleInterface $rule
     * @param array         $options
     *
     * @return LoadedRuleInterface
     */
    protected function loadRule(RuleInterface $rule, array $options)
    {
        $loadedRule = $this->loader->load($rule);
        if (!empty($options['selected_products'])) {
            $loadedRule->addCondition(
                [
                    'field' => 'id',
                    'operator' => 'IN',
                    'value' => $options['selected_products']
                ]
            );
        }

        return $loadedRule;
    }
}
