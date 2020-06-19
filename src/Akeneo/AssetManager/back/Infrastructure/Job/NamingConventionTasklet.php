<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\AbstractExecuteNamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConvention;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class NamingConventionTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ExecuteNamingConvention */
    private $executeNamingConvention;

    /**@var FindAssetIdentifiersByAssetFamilyInterface */
    private $findAssetIdentifiersByAssetFamily;

    public function __construct(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily
    ) {
        $this->executeNamingConvention = $executeNamingConvention;
        $this->findAssetIdentifiersByAssetFamily = $findAssetIdentifiersByAssetFamily;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->stepExecution->getJobParameters()->get('asset_family_identifier'));
        $this->stepExecution->addSummaryInfo('assets', 0);

        /** @var AssetIdentifier $assetIdentifier */
        foreach ($this->findAssetIdentifiersByAssetFamily->find($assetFamilyIdentifier) as $assetIdentifier) {
            try {
                $this->executeNamingConvention->executeOnAsset($assetFamilyIdentifier, $assetIdentifier);
                $this->stepExecution->incrementSummaryInfo('assets');
            } catch (AbstractExecuteNamingConventionException $exception) {
                $this->stepExecution->addWarning(
                    'pim_asset_manager.jobs.asset_manager_execute_naming_convention.error',
                    [
                        'asset' => (string)$assetIdentifier,
                    ],
                    new DataInvalidItem(['asset_identifier' => (string)$assetIdentifier])
                );
            }
        }
    }
}
