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

class RuleDefinitionUpdater implements RuleDefinitionUpdaterInterface
{
    public function fromRule(RuleDefinitionInterface $ruleDefinition, RuleInterface $rule): void
    {
        $ruleDefinition->setCode($rule->getCode());
        $ruleDefinition->setPriority($rule->getPriority());
        $ruleDefinition->setType($rule->getType());
        $ruleDefinition->setContent($rule->getContent());
        foreach ($rule->getTranslations() as $translation) {
            $ruleDefinition->setLabel($translation->getLocale(), $translation->getLabel());
        };
    }
}
