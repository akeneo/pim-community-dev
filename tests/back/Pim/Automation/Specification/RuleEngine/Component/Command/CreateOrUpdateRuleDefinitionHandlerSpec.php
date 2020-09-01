<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Command;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleDefinitionHandler;
use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CreateOrUpdateRuleDefinitionHandlerSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DenormalizerInterface $ruleDenormalizer,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver
    ) {
        $this->beConstructedWith(
            $ruleDefinitionRepository,
            $ruleDenormalizer,
            $ruleDefinitionUpdater,
            $ruleDefinitionSaver,
            Rule::class,
            RuleDefinition::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateOrUpdateRuleDefinitionHandler::class);
    }

    function it_creates_a_new_rule_definition(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DenormalizerInterface $ruleDenormalizer,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver,
        RuleInterface $rule
    ) {
        $command = new CreateOrUpdateRuleCommand(
            [
                'code' => 'new_rule',
                'conditions' => [],
                'actions' => [],
            ]
        );
        $ruleDefinitionRepository->findOneByIdentifier('new_rule')->willReturn(null);

        $ruleDenormalizer->denormalize(
            Argument::type('array'),
            Rule::class,
            null,
            Argument::cetera()
        )->shouldBeCalled()->willReturn($rule);

        $ruleDefinitionUpdater->fromRule(Argument::type(RuleDefinition::class), $rule)->shouldBeCalled();
        $ruleDefinitionSaver->save(Argument::type(RuleDefinition::class))->shouldBeCalled();

        $this->__invoke($command);
    }

    function it_updates_an_existing_rule_definition(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DenormalizerInterface $ruleDenormalizer,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionSaver $ruleDefinitionSaver
    ) {
        $command = new CreateOrUpdateRuleCommand(
            [
                'code' => 'my_rule',
                'priority' => 10,
                'conditions' => [],
                'actions' => [
                    [
                        'type' => 'set',
                        'field' => 'name',
                        'value' => 'Foo bar',
                    ],
                ],
            ]
        );

        $ruleDefinition = new RuleDefinition();
        $ruleDefinitionRepository->findOneByIdentifier('my_rule')->willReturn($ruleDefinition);

        $rule = new Rule($ruleDefinition);

        $ruleDenormalizer->denormalize(
            Argument::type('array'),
            Rule::class,
            null,
            Argument::cetera()
        )->shouldBeCalled()->willReturn($rule);

        $ruleDefinitionUpdater->fromRule($ruleDefinition, $rule)->shouldBeCalled();
        $ruleDefinitionSaver->save($ruleDefinition)->shouldBeCalled();

        $this->__invoke($command);
    }
}
