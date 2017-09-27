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
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Runs or dry run a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface DryRunnerInterface extends RunnerInterface
{
    /**
     * @param RuleDefinitionInterface $definition
     * @param array                   $options
     *
     * @return RuleSubjectSetInterface
     */
    public function dryRun(RuleDefinitionInterface $definition, array $options = []): ?RuleSubjectSetInterface;
}
