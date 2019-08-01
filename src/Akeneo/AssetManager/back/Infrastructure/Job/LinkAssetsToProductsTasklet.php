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

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\RuleTemplateExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Job tasklet than runs the rule execution of link assets to product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class LinkAssetsToProductsTasklet implements TaskletInterface
{
    /** @var RuleTemplateExecutor */
    private $ruleExecutor;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(RuleTemplateExecutor $ruleExecutor)
    {
        $this->ruleExecutor = $ruleExecutor;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->stepExecution->getJobParameters()->get('asset_family_identifier'));
        $assetCode = AssetCode::fromString($this->stepExecution->getJobParameters()->get('asset_code'));

        $this->ruleExecutor->execute($assetFamilyIdentifier, $assetCode);
    }
}
