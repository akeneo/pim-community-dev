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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class CriterionEvaluation
{
    /** @var CriterionCode */
    private $criterionCode;

    /** @var ProductId */
    private $productId;

    /** @var \DateTimeImmutable */
    private $evaluatedAt;

    /** @var CriterionEvaluationStatus */
    private $status;

    /** @var CriterionEvaluationResult|null */
    private $result;

    /** @var \DateTimeImmutable|null */
    private $startedAt;

    /** @var \DateTimeImmutable|null */
    private $endedAt;

    public function __construct(
        CriterionCode $criterionCode,
        ProductId $productId,
        ?\DateTimeImmutable $evaluatedAt,
        CriterionEvaluationStatus $status,
        ?CriterionEvaluationResult $result
    ) {
        $this->criterionCode = $criterionCode;
        $this->productId = $productId;
        $this->evaluatedAt = $evaluatedAt;
        $this->status = $status;
        $this->result = $result;
    }

    public function getCriterionCode(): CriterionCode
    {
        return $this->criterionCode;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getEvaluatedAt(): ?\DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getStatus(): CriterionEvaluationStatus
    {
        return $this->status;
    }

    public function getResult(): ?CriterionEvaluationResult
    {
        return $this->result;
    }
}
