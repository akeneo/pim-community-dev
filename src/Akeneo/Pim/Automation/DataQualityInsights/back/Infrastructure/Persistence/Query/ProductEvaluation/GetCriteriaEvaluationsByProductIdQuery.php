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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class GetCriteriaEvaluationsByProductIdQuery implements GetCriteriaEvaluationsByProductIdQueryInterface
{
    /** @var Connection */
    private $db;

    /** @var Clock */
    private $clock;

    /** @var string */
    private $tableName;

    /** @var TransformCriterionEvaluationResultIds */
    private $transformCriterionEvaluationResultIds;

    public function __construct(
        Connection $db,
        Clock $clock,
        TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds,
        string $tableName
    ) {
        $this->db = $db;
        $this->clock = $clock;
        $this->tableName = $tableName;
        $this->transformCriterionEvaluationResultIds = $transformCriterionEvaluationResultIds;
    }

    public function execute(ProductId $productId): Read\CriterionEvaluationCollection
    {
        $criterionEvaluationTable = $this->tableName;

        $sql = <<<SQL
SELECT
       evaluation.product_id,
       evaluation.criterion_code,
       evaluation.status,
       evaluation.evaluated_at,
       evaluation.result
FROM $criterionEvaluationTable AS evaluation
WHERE evaluation.product_id = :product_id
SQL;

        $rows = $this->db->executeQuery(
            $sql,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        )->fetchAll(FetchMode::ASSOCIATIVE);

        $criteriaEvaluations = new Read\CriterionEvaluationCollection();
        foreach ($rows as $rawCriterionEvaluation) {
            $criteriaEvaluations->add(new Read\CriterionEvaluation(
                new CriterionCode($rawCriterionEvaluation['criterion_code']),
                new ProductId(intval($rawCriterionEvaluation['product_id'])),
                null !== $rawCriterionEvaluation['evaluated_at'] ? $this->clock->fromString($rawCriterionEvaluation['evaluated_at']) : null,
                new CriterionEvaluationStatus($rawCriterionEvaluation['status']),
                $this->hydrateCriterionEvaluationResult($rawCriterionEvaluation['result']),
            ));
        }

        return $criteriaEvaluations;
    }

    private function hydrateCriterionEvaluationResult($rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($rawResult);

        $rates = ChannelLocaleRateCollection::fromArrayInt($rawResult['rates'] ?? []);
        $status = CriterionEvaluationResultStatusCollection::fromArrayString($rawResult['status'] ?? []);

        return new Read\CriterionEvaluationResult($rates, $status, $rawResult['data'] ?? []);
    }
}
