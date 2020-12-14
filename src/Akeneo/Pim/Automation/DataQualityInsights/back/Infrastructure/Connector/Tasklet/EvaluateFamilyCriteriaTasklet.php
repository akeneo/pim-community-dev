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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateFamilyAttributesSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetFamilyIdsToEvaluateQuery;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class EvaluateFamilyCriteriaTasklet implements TaskletInterface
{
    private const BULK_SIZE = 100;

    /** @var StepExecution */
    private $stepExecution;

    private GetFamilyIdsToEvaluateQuery $getFamilyIdsToEvaluate;

    private EvaluateFamilyAttributesSpelling $evaluateFamilyAttributesSpelling;

    private LoggerInterface $logger;

    public function __construct(
        GetFamilyIdsToEvaluateQuery $getFamilyIdsToEvaluate,
        EvaluateFamilyAttributesSpelling $evaluateFamilyAttributesSpelling,
        LoggerInterface $logger
    ) {
        $this->getFamilyIdsToEvaluate = $getFamilyIdsToEvaluate;
        $this->evaluateFamilyAttributesSpelling = $evaluateFamilyAttributesSpelling;
        $this->logger = $logger;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        try {
            foreach ($this->getFamilyIdsToEvaluate->execute(self::BULK_SIZE) as $familyIds) {
                array_walk($familyIds, function (FamilyId $familyId) {
                    $this->evaluateFamilyAttributesSpelling->evaluate($familyId);
                });
            }
        } catch (\Exception $exception) {
            if (null !== $this->stepExecution) {
                $this->stepExecution->addFailureException($exception);
            }
            $this->logger->error('An error occurred during families evaluations.', [
                'error_code' => 'families_evaluation_failed',
                'error_message' => $exception->getMessage(),
                'step_execution_id' => $this->stepExecution->getId(),
            ]);
        }
    }
}
