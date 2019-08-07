<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\RuleTemplates;

use Akeneo\AssetManager\Application\Asset\LinkAssets\CompiledRuleRunnerInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\CompiledRule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleEngineRunner implements CompiledRuleRunnerInterface
{
    /** @var DenormalizerInterface */
    private $ruleDenormalizer;

    /** @var RunnerInterface */
    private $ruleRunner;

    public function __construct(
        DenormalizerInterface $ruleDenormalizer,
        RunnerInterface $ruleRunner
    ) {
        $this->ruleDenormalizer = $ruleDenormalizer;
        $this->ruleRunner = $ruleRunner;
    }

    public function run(CompiledRule $compiledRule): void
    {
        $normalizedRule = $this->adaptCompiledRuleToRuleEngine($compiledRule);
        $rule = $this->ruleDenormalizer->denormalize($normalizedRule, Rule::class);
        $this->ruleRunner->run($rule);
    }

    private function adaptCompiledRuleToRuleEngine(CompiledRule $compiledRule): array
    {
        return [
            'code'       => '',
            'priority'   => '',
            'conditions' => $compiledRule->getConditions(),
            'actions'    => $compiledRule->getActions()
        ];
    }
}
