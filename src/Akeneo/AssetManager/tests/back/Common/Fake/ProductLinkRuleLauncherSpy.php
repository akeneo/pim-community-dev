<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\ProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PHPUnit\Framework\Assert;

class ProductLinkRuleLauncherSpy implements ProductLinkRuleLauncherInterface
{
    private $timesRulesLaunched = 0;

    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): void
    {
        $this->timesRulesLaunched++;
    }

    public function assertHasRunOnce(): void
    {
        Assert::assertTrue($this->timesRulesLaunched === 1);
    }
}
