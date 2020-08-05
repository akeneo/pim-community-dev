<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Updater;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;

interface RuleDefinitionUpdaterInterface
{
    /**
     * @param RuleDefinitionInterface $ruleDefinition
     * @param RuleInterface $rule
     */
    public function fromRule(RuleDefinitionInterface $ruleDefinition, RuleInterface $rule): void;
}
