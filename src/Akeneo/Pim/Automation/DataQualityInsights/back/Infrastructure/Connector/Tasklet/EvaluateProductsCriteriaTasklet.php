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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

final class EvaluateProductsCriteriaTasklet implements TaskletInterface
{
    public const JOB_INSTANCE_NAME = 'data_quality_insights_evaluate_products_criteria';

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var StepExecution */
    private $stepExecution;

    /** @var ConsolidateProductAxisRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    public function __construct(EvaluatePendingCriteria $evaluatePendingCriteria, ConsolidateProductAxisRates $consolidateProductAxisRates, IndexProductRates $indexProductRates)
    {
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
    }

    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $productIds = $jobParameters->get(EvaluateProductsCriteriaParameters::PRODUCT_IDS);

        $this->evaluatePendingCriteria->execute($productIds);

        $this->consolidateProductAxisRates->consolidate($productIds);

        $this->indexProductRates->execute($productIds);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
