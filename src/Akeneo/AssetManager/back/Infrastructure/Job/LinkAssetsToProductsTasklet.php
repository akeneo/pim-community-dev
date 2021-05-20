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

use Akeneo\AssetManager\Application\Asset\LinkAssets\RuleTemplateExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetCodesByAssetFamilyInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Job tasklet than runs the rule execution of link assets to product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class LinkAssetsToProductsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private RuleTemplateExecutor $ruleTemplateExecutor;
    private FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily;
    private ?StepExecution $stepExecution = null;
    private CountAssetsInterface $countAssets;
    private JobRepositoryInterface $jobRepository;
    private int $batchSize;

    public function __construct(
        RuleTemplateExecutor $ruleExecutor,
        FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository,
        int $batchSize
    ) {
        $this->ruleTemplateExecutor = $ruleExecutor;
        $this->findAssetCodesByAssetFamily = $findAssetCodesByAssetFamily;
        $this->countAssets = $countAssets;
        $this->jobRepository = $jobRepository;
        $this->batchSize = $batchSize;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    public function execute(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->stepExecution->getJobParameters()->get('asset_family_identifier'));

        if ($this->stepExecution->getJobParameters()->has('asset_codes')) {
            $assetCodes = array_map(fn(string $assetCode) => AssetCode::fromString($assetCode), $this->stepExecution->getJobParameters()->get('asset_codes'));
            $totalItems = count($assetCodes);
        } else {
            $assetCodes = $this->findAssetCodesByAssetFamily->find($assetFamilyIdentifier);
            $totalItems = $this->countAssets->forAssetFamily($assetFamilyIdentifier);
        }

        $batchCount = 0;
        $this->stepExecution->setTotalItems($totalItems);
        foreach ($assetCodes as $assetCode) {
            $batchCount++;
            try {
                $errors = $this->ruleTemplateExecutor->execute($assetFamilyIdentifier, $assetCode);
                foreach ($errors as $error) {
                    $this->stepExecution->addWarning($error, [], new DataInvalidItem(['asset_code' => (string)$assetCode]));
                }
            } catch (\InvalidArgumentException $e) {
                $message = sprintf('The asset could not be linked to products: %s', $e->getMessage());
                $this->stepExecution->addWarning($message, [], new DataInvalidItem(['asset_code' => (string)$assetCode]));
            }

            $this->stepExecution->incrementProcessedItems();

            if ($batchCount >= $this->batchSize) {
                $this->jobRepository->updateStepExecution($this->stepExecution);
                $batchCount = 0;
            }
        }

        if ($batchCount > 0) {
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }
}
