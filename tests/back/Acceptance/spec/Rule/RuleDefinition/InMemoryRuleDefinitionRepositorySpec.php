<?php

namespace spec\AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition\InMemoryRuleDefinitionRepository;
use PhpSpec\ObjectBehavior;

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
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }
}
