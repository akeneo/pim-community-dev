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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class CriterionEvaluation
{
    /**
     * @var CriterionEvaluationId
     */
    private $id;

    /**
     * @var CriterionCode
     */
    private $criterionCode;

    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $startedAt;

    /**
     * @var \DateTimeImmutable
     */
    private $endedAt;

    /**
     * @var CriterionEvaluationStatus
     */
    private $status;

    /**
     * @var CriterionEvaluationResult
     */
    private $result;

    public function __construct(
        CriterionEvaluationId $id,
        CriterionCode $criterionCode,
        ProductId $productId,
        \DateTimeImmutable $createdAt,
        CriterionEvaluationStatus $status
    ) {
        $this->criterionCode = $criterionCode;
        $this->productId = $productId;
        $this->createdAt = $createdAt;
        $this->status = $status;

        $this->startedAt = null;
        $this->endedAt = null;
        $this->result = null;
        $this->id = $id;
    }

    public function start(): self
    {
        $this->startedAt = new \DateTimeImmutable();
        $this->status = CriterionEvaluationStatus::inProgress();

        return $this;
    }

    public function end(CriterionEvaluationResult $result): self
    {
        $this->endedAt = new \DateTimeImmutable();
        $this->status = CriterionEvaluationStatus::done();
        $this->result = $result;

        return $this;
    }

    public function applicabilityEvaluated(CriterionApplicability $criterionApplicability): self
    {
        $this->result = $criterionApplicability->getEvaluationResult();
        $this->status = CriterionEvaluationStatus::pending();

        if (false === $criterionApplicability->isApplicable()) {
            $this->startedAt = new \DateTimeImmutable();
            $this->endedAt = new \DateTimeImmutable();
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

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): CriterionEvaluationStatus
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function getResult(): ?CriterionEvaluationResult
    {
        return $this->result;
    }

    public function getId(): CriterionEvaluationId
    {
        return $this->id;
    }
}
