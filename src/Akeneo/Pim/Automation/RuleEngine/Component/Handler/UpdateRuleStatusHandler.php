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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Handler;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\UpdateRuleStatusCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\RuleNotFoundException;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class UpdateRuleStatusHandler
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionSaver = $ruleDefinitionSaver;
    }

    public function __invoke(UpdateRuleStatusCommand $command): void
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($command->getCode());
        if (null === $ruleDefinition) {
            throw new RuleNotFoundException();
        }
        if ($ruleDefinition->isEnabled() === $command->isEnabled()) {
            return;
        }

        $ruleDefinition->setEnabled($command->isEnabled());
        $this->ruleDefinitionSaver->save($ruleDefinition);
    }
}
