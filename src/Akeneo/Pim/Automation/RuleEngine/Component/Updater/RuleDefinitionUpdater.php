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
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

class RuleDefinitionUpdater implements ObjectUpdaterInterface
{
    public function update($ruleDefinition, array $data, array $options = []): void
    {
        Assert::isInstanceOf($ruleDefinition, RuleDefinitionInterface::class);

        /** @var $ruleDefinition RuleDefinitionInterface */
        $ruleDefinition->setCode($data['code']);
        $ruleDefinition->setType($data['type']);
        $ruleDefinition->setPriority($data['priority']);
        $ruleDefinition->setContent($data['content']);
    }
}
