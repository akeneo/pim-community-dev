<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

use Akeneo\AssetManager\Domain\Model\AssetFamily\CompiledRule;

interface CompiledRuleRunnerInterface
{
    public function run(CompiledRule $compiledRule): void;
}
