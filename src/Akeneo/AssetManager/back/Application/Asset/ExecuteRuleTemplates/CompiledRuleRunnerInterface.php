<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

interface CompiledRuleRunnerInterface
{
    public function run(CompiledRule $compiledRule): void;
}
