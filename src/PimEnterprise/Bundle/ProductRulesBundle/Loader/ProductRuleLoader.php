<?php

namespace Pim\Bundle\ProductRulesBundle\Loader;

use Pim\Bundle\RuleInterface\Model\RuleInterface;
use Pim\Bundle\RuleInterface\Loader\LoaderInterface;
use Pim\Bundle\ProductRulesBundle\Model\ProductRuleInterface;
use Pim\Bundle\ProductRulesBundle\Model\ProductRule;

class ProductRuleLoader implements LoaderInterface
{
    /**
     * @return ProductRuleInterface
     */
    public function load(RuleInstanceInterface $instance)
    {
        $rule = new ProductRule();

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

    public function supports(RuleInstanceInterface $instance)
    {
        return $instance->getRuleFQCN() === 'Pim\Bundle\ProductRulesBundle\Model\ProductRule';
    }
}
