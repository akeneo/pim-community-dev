<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleDecorator;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleRepositoryInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $rule, array $context = [])
    {
        //TODO: do not hardcode this
        $loaded = new LoadedRuleDecorator($rule);

        $content = json_decode($rule->getContent(), true);
        $loaded->setConditions($content['conditions']);

        return $loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule, array $context = [])
    {
        return 'product' === $rule->getType();
    }
}
