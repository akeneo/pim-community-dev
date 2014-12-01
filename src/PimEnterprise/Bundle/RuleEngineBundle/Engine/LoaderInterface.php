<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Engine;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Loads a rule defintion to make to be able to apply it.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface LoaderInterface
{
    /**
     * @param RuleDefinitionInterface $definition
     *
     * @return RuleInterface
     */
    public function load(RuleDefinitionInterface $definition);

    /**
     * @param RuleDefinitionInterface $definition
     *
     * @return bool
     */
    public function supports(RuleDefinitionInterface $definition);
}
