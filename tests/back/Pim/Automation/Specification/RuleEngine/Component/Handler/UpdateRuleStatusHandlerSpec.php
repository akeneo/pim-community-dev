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

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Handler;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\UpdateRuleStatusCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\RuleNotFoundException;
use Akeneo\Pim\Automation\RuleEngine\Component\Handler\UpdateRuleStatusHandler;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use PhpSpec\ObjectBehavior;

class UpdateRuleStatusHandlerSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver
    ) {
        $this->beConstructedWith($ruleDefinitionRepository, $ruleDefinitionSaver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateRuleStatusHandler::class);
    }

    function it_enables_a_rule(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinitionRepository->findOneByIdentifier('code')->willReturn($ruleDefinition);
        $ruleDefinition->isEnabled()->willReturn(false);

        $ruleDefinition->setEnabled(true)->shouldBeCalled();
        $ruleDefinitionSaver->save($ruleDefinition)->shouldBeCalled();

        $command = new UpdateRuleStatusCommand('code', true);
        $this->__invoke($command);
    }

    function it_disables_a_rule(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinitionRepository->findOneByIdentifier('code')->willReturn($ruleDefinition);
        $ruleDefinition->isEnabled()->willReturn(true);

        $ruleDefinition->setEnabled(false)->shouldBeCalled();
        $ruleDefinitionSaver->save($ruleDefinition)->shouldBeCalled();

        $command = new UpdateRuleStatusCommand('code', false);
        $this->__invoke($command);
    }

    function it_throw_an_exception_when_the_rule_is_not_found(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository
    ) {
        $ruleDefinitionRepository->findOneByIdentifier('code')->willReturn(null);

        $command = new UpdateRuleStatusCommand('code', false);
        $this->shouldThrow(new RuleNotFoundException())->during('__invoke', [$command]);
    }

    function it_does_nothing_when_the_rule_is_already_enabled(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinitionRepository->findOneByIdentifier('code')->willReturn($ruleDefinition);
        $ruleDefinition->isEnabled()->willReturn(true);

        $command = new UpdateRuleStatusCommand('code', true);
        $this->__invoke($command);
    }

    function it_does_nothing_when_the_rule_is_already_disabled(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinitionRepository->findOneByIdentifier('code')->willReturn($ruleDefinition);
        $ruleDefinition->isEnabled()->willReturn(false);

        $command = new UpdateRuleStatusCommand('code', false);
        $this->__invoke($command);
    }
}
