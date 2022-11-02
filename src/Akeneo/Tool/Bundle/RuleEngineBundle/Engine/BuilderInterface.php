<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Engine;

use Akeneo\Tool\Bundle\RuleEngineBundle\Exception\BuilderException;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;

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
     * @throws BuilderException
     *
     * @return RuleInterface
     */
    public function build(RuleDefinitionInterface $definition);
}
