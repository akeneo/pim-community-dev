<?php

namespace spec\AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition\InMemoryRuleDefinitionRepository;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\ExpectationFailedException;

class InMemoryRuleDefinitionRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryRuleDefinitionRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_a_rule_definition_repository()
    {
        $this->shouldBeAnInstanceOf(RuleDefinitionRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_rule_definition_by_identifier()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition->setCode('a-rule-definition');
        $this->beConstructedWith([$ruleDefinition->getCode() => $ruleDefinition]);

        $this->findOneByIdentifier('a-rule-definition')->shouldReturn($ruleDefinition);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-rule')->shouldReturn(null);
    }

    function it_saves_a_rule_definition()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition->setCode('a-rule-definition');

        $this->save($ruleDefinition)->shouldReturn(null);

        $this->findOneByIdentifier($ruleDefinition->getCode())->shouldReturn($ruleDefinition);
    }

    function it_saves_only_rule_definitions()
    {
        $this
            ->shouldThrow(ExpectationFailedException::class)
            ->during('save', ['a_thing']);
    }

    function it_returns_enabled_rule_definitions_ordered_by_priority()
    {
        $ruleDefinition1 = new RuleDefinition();
        $ruleDefinition1->setCode('rule1');
        $ruleDefinition1->setPriority(100);
        $this->save($ruleDefinition1);
        $ruleDefinition2 = new RuleDefinition();
        $ruleDefinition2->setCode('rule2');
        $ruleDefinition2->setPriority(300);
        $this->save($ruleDefinition2);
        $ruleDefinition3 = new RuleDefinition();
        $ruleDefinition3->setCode('rule3');
        $ruleDefinition3->setPriority(200);
        $this->save($ruleDefinition3);
        $ruleDefinition4 = new RuleDefinition();
        $ruleDefinition4->setCode('rule4');
        $ruleDefinition4->setPriority(200);
        $ruleDefinition4->setEnabled(false);
        $this->save($ruleDefinition4);

        $list = $this->findEnabledOrderedByPriority();
        $list->shouldBe([$ruleDefinition2, $ruleDefinition3, $ruleDefinition1]);
        $list[0]->getPriority()->shouldBe(300);
        $list[1]->getPriority()->shouldBe(200);
        $list[2]->getPriority()->shouldBe(100);
    }
}
