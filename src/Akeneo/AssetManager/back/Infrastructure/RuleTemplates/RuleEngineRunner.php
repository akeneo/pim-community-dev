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
    public function __construct(
        private DenormalizerInterface $ruleDenormalizer,
        private RunnerInterface $ruleRunner
    ) {
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
            'conditions' => $this->adaptConditionsToRuleEngine($compiledRule->getConditions()),
            'actions'    => $this->adaptActionsToRuleEngine($compiledRule->getActions())
        ];
    }

    private function adaptConditionsToRuleEngine(array $conditions): array
    {
        return array_map(
            static function (array $condition) {
                $condition['scope'] = $condition['channel'];

                return $condition;
            },
            $conditions
        );
    }

    /**
     * The RuleEngine either needs a `value` or `items` property depending on the action type (`add` or `set`).
     * In our case, we will always set both properties.
     */
    private function adaptActionsToRuleEngine(array $actions): array
    {
        return array_map(
            static function (array $action) {
                $action['value'] = $action['items'];
                $action['scope'] = $action['channel'];

                return $action;
            },
            $actions
        );
    }
}
