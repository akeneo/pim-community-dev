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

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class ComputeTransformations implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ComputeTransformationsExecutor */
    private $computeTransformationsExecutor;

    public function __construct(ComputeTransformationsExecutor $computeTransformationsExecutor)
    {
        $this->computeTransformationsExecutor = $computeTransformationsExecutor;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $this->computeTransformationsExecutor->execute(array_map(function (string $assetIdentifier) {
            return AssetIdentifier::fromString($assetIdentifier);
        }, $this->stepExecution->getJobParameters()->get('asset_identifiers')));
    }
}
