<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Loader;

use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRunnableRule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Loader\LoaderInterface;

class ProductRuleLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $instance)
    {
        $rule = new ProductRunnableRule();

        // load expression from content
        $jsonContent = $instance->getContent();
        $content = json_decode($jsonContent, true);
        $expression = $content['expression'];

        // TODO : load/transform from expression to QB
        $rule->setExpression($expression);

        // TODO : load actions, they may be in expression too
        $actions = $content['actions'];

        // use a ProductRuleBuilder
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'Product' === $rule->getType();
    }
}
