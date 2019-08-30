<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Domain\Model\AssetFamily\CompiledRule;

interface CompiledRuleRunnerInterface
{
    public function run(CompiledRule $compiledRule): void;
}
