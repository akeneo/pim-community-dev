<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\Database;

use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
final class AssetWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private CreateAssetHandler $createAssetHandler;
    private EditAssetHandler $editAssetHandler;
    private ?StepExecution $stepExecution = null;

    public function __construct(CreateAssetHandler $createAssetHandler, EditAssetHandler $editAssetHandler)
    {
        $this->createAssetHandler = $createAssetHandler;
        $this->editAssetHandler = $editAssetHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $createAndEditAssetCommands): void
    {
        if (0 === count($createAndEditAssetCommands)) {
            return;
        }

        foreach ($createAndEditAssetCommands as $createAndEditAssetCommand) {
            Assert::isInstanceOf($createAndEditAssetCommand, CreateAndEditAssetCommand::class);

            if ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo(
                    $createAndEditAssetCommand->createAssetCommand ? 'create' : 'process'
                );
            }

            if ($createAndEditAssetCommand->createAssetCommand) {
                ($this->createAssetHandler)($createAndEditAssetCommand->createAssetCommand);
            }

            ($this->editAssetHandler)($createAndEditAssetCommand->editAssetCommand);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
