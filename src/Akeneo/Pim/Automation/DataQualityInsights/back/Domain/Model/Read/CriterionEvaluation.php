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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class CriterionEvaluation
{
    /** @var CriterionEvaluationId */
    private $id;

    /** @var CriterionCode */
    private $criterionCode;

    /** @var ProductId */
    private $productId;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var CriterionEvaluationStatus */
    private $status;

    /** @var CriterionEvaluationResult|null */
    private $result;

    /** @var \DateTimeImmutable|null */
    private $startedAt;

    /** @var \DateTimeImmutable|null */
    private $endedAt;

    public function __construct(
        CriterionEvaluationId $id,
        CriterionCode $criterionCode,
        ProductId $productId,
        \DateTimeImmutable $createdAt,
        CriterionEvaluationStatus $status,
        ?CriterionEvaluationResult $result,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $endedAt
    ) {
        $this->id = $id;
        $this->criterionCode = $criterionCode;
        $this->productId = $productId;
        $this->createdAt = $createdAt;
        $this->status = $status;
        $this->result = $result;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
    }

    public function getId(): CriterionEvaluationId
    {
        return $this->id;
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
}
