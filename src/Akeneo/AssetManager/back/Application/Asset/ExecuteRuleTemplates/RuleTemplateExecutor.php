<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Query\Asset\FindPropertyAccessibleAssetInterface;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;

class RuleTemplateExecutor
{
    /** @var RuleCompiler */
    private $ruleCompiler;

    /** @var RunnerInterface */
    private $compiledRuleRunner;

    /** @var FindPropertyAccessibleAssetInterface */
    private $findPropertyAccessibleAsset;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        FindPropertyAccessibleAssetInterface $findPropertyAccessibleAsset,
        RuleCompiler $ruleCompiler,
        CompiledRuleRunnerInterface $compiledRuleRunner
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->findPropertyAccessibleAsset = $findPropertyAccessibleAsset;
        $this->ruleCompiler = $ruleCompiler;
        $this->compiledRuleRunner = $compiledRuleRunner;
    }

    public function execute(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): void
    {
        $asset = $this->findPropertyAccessibleAsset->find($assetFamilyIdentifier, $assetCode);
        $ruleTemplateCollection = $this->ruleTemplateCollection($assetFamilyIdentifier);
        foreach ($ruleTemplateCollection as $ruleTemplate) {
            $this->run($ruleTemplate, $asset);
        }
    }

    private function ruleTemplateCollection(AssetFamilyIdentifier $assetFamilyIdentifier): RuleTemplateCollection
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $ruleTemplates = $assetFamily->getRuleTemplateCollection();

        return $ruleTemplates;
    }

    private function run($ruleTemplate, ?PropertyAccessibleAsset $asset): void
    {
        $rule = $this->ruleCompiler->compile($ruleTemplate, $asset);
        $this->compiledRuleRunner->run($rule);
    }
}
