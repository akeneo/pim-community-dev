<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\RuleTemplates;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRule;
use Akeneo\AssetManager\Infrastructure\RuleTemplates\RuleEngineSynchronizedRunner;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleEngineSynchronizedRunnerSpec extends ObjectBehavior
{
    public function let(
        DenormalizerInterface $ruleDenormalizer,
        RunnerInterface $ruleEngineRunner
    ) {
        $this->beConstructedWith($ruleDenormalizer, $ruleEngineRunner);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RuleEngineSynchronizedRunner::class);
    }

    function it_run_a_compiled_rule(
        DenormalizerInterface $ruleDenormalizer,
        RunnerInterface $ruleEngineRunner,
        Rule $rule
    ) {
        $conditions = ['conditions'];
        $actions = ['actions'];
        $compiledRule = new CompiledRule($conditions, $actions);

        $ruleDenormalizer->denormalize(
            [
                'code'       => '',
                'priority'   => '',
                'conditions' => $conditions,
                'actions'    => $actions
            ],
            Rule::class
        )->willReturn($rule);
        $ruleEngineRunner->run($rule);

        $this->run($compiledRule);
    }
}
