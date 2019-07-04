<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRule;
use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRuleRunnerInterface;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompiledRuleRunnerSpy implements CompiledRuleRunnerInterface
{
    /** @var CompiledRule[] */
    private $compiledRules = [];

    public function run(CompiledRule $compiledRule): void
    {
        $this->compiledRules[] = $compiledRule;
    }

    public function assertHasRunTimes(int $expectedTimesRun): void
    {
        $actualTimesRun = \count($this->compiledRules);
        Assert::assertEquals(
            $expectedTimesRun,
            $actualTimesRun,
            sprintf(
                'Expected rule runner to run %d times, %d given',
                $expectedTimesRun,
                $actualTimesRun
            )
        );
    }

    public function assertLastCompiledRule(CompiledRule $expectedCompiledRule): void
    {
        $actualCompiledRule = \end($this->compiledRules);
        Assert::assertEquals($expectedCompiledRule->getConditions(), $actualCompiledRule->getConditions());
        Assert::assertEquals($expectedCompiledRule->getActions(), $actualCompiledRule->getActions());
    }
}
