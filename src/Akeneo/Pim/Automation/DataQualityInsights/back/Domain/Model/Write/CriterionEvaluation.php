<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionEvaluation
{
    private ?\DateTimeImmutable $evaluatedAt = null;
    private ?CriterionEvaluationResult $result = null;

    public function __construct(
        private CriterionCode             $criterionCode,
        private ProductEntityIdInterface  $entityId,
        private CriterionEvaluationStatus $status
    ) {
    }

    public function start(): self
    {
        $this->status = CriterionEvaluationStatus::inProgress();

        return $this;
    }

    public function end(CriterionEvaluationResult $result): self
    {
        $this->status = CriterionEvaluationStatus::done();
        $this->evaluatedAt = new \DateTimeImmutable();
        $this->result = $result;

        return $this;
    }

    public function applicabilityEvaluated(CriterionApplicability $criterionApplicability): self
    {
        $this->result = $criterionApplicability->getEvaluationResult();
        $this->status = CriterionEvaluationStatus::pending();

        if (false === $criterionApplicability->isApplicable()) {
            $this->evaluatedAt = new \DateTimeImmutable();
            $this->status = CriterionEvaluationStatus::done();
        }

        return $this;
    }

    public function flagAsError(): self
    {
        $this->status = CriterionEvaluationStatus::error();

        return $this;
    }

    public function flagsAsTimeout(): self
    {
        $this->status = CriterionEvaluationStatus::timeout();

        return $this;
    }

    public function getCriterionCode(): CriterionCode
    {
        return $this->criterionCode;
    }

    public function getEntityId(): ProductEntityIdInterface
    {
        return $this->entityId;
    }

    public function getEvaluatedAt(): ?\DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getStatus(): CriterionEvaluationStatus
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function getResult(): ?CriterionEvaluationResult
    {
        return $this->result;
    }
}
