<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Engine;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Build a rule from a rule definition to be able to apply it.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface BuilderInterface
{
    /**
     * @param RuleDefinitionInterface $definition
     *
     * @throws \Akeneo\Bundle\RuleEngineBundle\Exception\BuilderException
     *
     * @return RuleInterface
     */
    public function build(RuleDefinitionInterface $definition);
}
