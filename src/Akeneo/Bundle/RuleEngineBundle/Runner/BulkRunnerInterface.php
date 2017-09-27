<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Runner;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Runs a set of rules.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
interface BulkRunnerInterface
{
    /**
     * Runs a set of rules.
     *
     * @param RuleDefinitionInterface[] $definitions
     * @param array                     $options
     *
     * @return array
     */
    public function runAll(array $definitions, array $options = []): array;
}
