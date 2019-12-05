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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters\PushStructureAndProductsToFranklinParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

final class PushStructureAndProductsToFranklinTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var GetConnectionIsActiveHandler */
    private $connectionIsActiveHandler;

    /** @var PushStructureAndProductsToFranklin */
    private $pushStructureAndProductsToFranklin;

    public function __construct(
        GetConnectionIsActiveHandler $connectionIsActiveHandler,
        PushStructureAndProductsToFranklin $pushStructureAndProductsToFranklin
    ) {
        $this->connectionIsActiveHandler = $connectionIsActiveHandler;
        $this->pushStructureAndProductsToFranklin = $pushStructureAndProductsToFranklin;
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->isFranklinInsightsActivated()) {
            throw new \Exception('Franklin Insights is not activated.');
        }

        $jobParameters = $this->stepExecution->getJobParameters();
        $attributesBatchSize = $jobParameters->get(PushStructureAndProductsToFranklinParameters::ATTRIBUTES_BATCH_SIZE);
        $familiesBatchSize = $jobParameters->get(PushStructureAndProductsToFranklinParameters::FAMILIES_BATCH_SIZE);
        $productsBatchSize = $jobParameters->get(PushStructureAndProductsToFranklinParameters::PRODUCTS_BATCH_SIZE);

        $this->pushStructureAndProductsToFranklin->push(
            new BatchSize($attributesBatchSize),
            new BatchSize($familiesBatchSize),
            new BatchSize($productsBatchSize)
        );
    }

    private function isFranklinInsightsActivated(): bool
    {
        return $this->connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery());
    }
}
