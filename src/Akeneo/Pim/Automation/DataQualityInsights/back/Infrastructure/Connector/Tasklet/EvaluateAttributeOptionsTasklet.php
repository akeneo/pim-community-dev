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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributeOptions;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class EvaluateAttributeOptionsTasklet implements TaskletInterface
{
    use EvaluationJobAwareTrait;

    /** @var EvaluateUpdatedAttributeOptions */
    private $evaluateUpdatedAttributeOptions;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EvaluateUpdatedAttributeOptions $evaluateUpdatedAttributeOptions, LoggerInterface $logger)
    {
        $this->evaluateUpdatedAttributeOptions = $evaluateUpdatedAttributeOptions;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->evaluateUpdatedAttributeOptions->evaluateSince($this->updatedSince());
        } catch (\Exception $exception) {
            null !== $this->stepExecution && $this->stepExecution->addFailureException($exception);
            $this->logger->error('The evaluations of the attribute options has failed', [
                'error_code' => 'attribute_options_evaluation_failed',
                'error_message' => $exception->getMessage(),
                'step_execution_id' => $this->stepExecution->getId(),
            ]);
        }
    }
}
