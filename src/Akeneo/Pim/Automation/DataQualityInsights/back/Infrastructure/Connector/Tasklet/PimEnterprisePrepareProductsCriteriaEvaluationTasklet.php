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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateMissingCriteriaEvaluationsInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class PimEnterprisePrepareProductsCriteriaEvaluationTasklet implements TaskletInterface
{
    use EvaluationJobAwareTrait;

    private const BULK_SIZE = 100;

    /** @var CreateMissingCriteriaEvaluationsInterface */
    private $createMissingProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CreateMissingCriteriaEvaluationsInterface $createMissingProductsCriteriaEvaluations,
        LoggerInterface $logger
    ) {
        $this->createMissingProductsCriteriaEvaluations = $createMissingProductsCriteriaEvaluations;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->createMissingCriteriaEvaluations();
    }

    private function createMissingCriteriaEvaluations(): void
    {
        try {
            $updatedSince = $this->updatedSince();
            $this->createMissingProductsCriteriaEvaluations->createForProductsUpdatedSince($updatedSince, self::BULK_SIZE);
            $this->createMissingProductsCriteriaEvaluations->createForProductsImpactedByStructureUpdatedSince($updatedSince, self::BULK_SIZE);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to create all missing criteria evaluations for the products',
                [
                    'error_code' => 'unable_to_create_missing_product_criteria_evaluation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }
}
