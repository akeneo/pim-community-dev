<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Runner;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Runs a rule.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface RunnerInterface
{
    /**
     * @param RuleDefinitionInterface $definition
     * @param array                   $options
     *
     * @return mixed
     */
    public function run(RuleDefinitionInterface $definition, array $options = []);

    /**
     * @param RuleDefinitionInterface $definition
     *
     * @return bool
     */
    public function supports(RuleDefinitionInterface $definition): bool;
}
