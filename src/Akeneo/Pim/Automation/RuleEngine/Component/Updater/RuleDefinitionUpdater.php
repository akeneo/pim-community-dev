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

class RuleDefinitionUpdater implements RuleDefinitionUpdaterInterface
{
    public function update(RuleDefinitionInterface $ruleDefinition, array $data): void
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'code':
                    $ruleDefinition->setCode($data['code']);
                    break;
                case 'type':
                    $ruleDefinition->setType($data['type']);
                    break;
                case 'priority':
                    $ruleDefinition->setPriority($data['priority']);
                    break;
                case 'content':
                    $ruleDefinition->setContent($data['content']);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown "%s" property', $key));
            }
        }
    }
}
