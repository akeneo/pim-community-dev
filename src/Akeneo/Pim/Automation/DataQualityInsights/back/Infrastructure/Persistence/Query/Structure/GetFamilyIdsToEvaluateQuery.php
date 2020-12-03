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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamilyIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Doctrine\DBAL\Connection;

final class GetFamilyIdsToEvaluateQuery implements GetFamilyIdsToEvaluateQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(int $bulkSize): \Iterator
    {
        $query = <<<SQL
SELECT DISTINCT family.id
FROM pim_catalog_family AS family
    LEFT JOIN pimee_dqi_family_criteria_evaluation AS family_evaluation ON family_evaluation.family_id = family.id
WHERE family_evaluation.family_id IS NULL
    OR family_evaluation.evaluated_at < family.updated
    OR EXISTS(
        SELECT 1 FROM pim_catalog_family_attribute AS family_attribute
            INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = family_attribute.attribute_id
            INNER JOIN pimee_dqi_attribute_spellcheck AS attribute_spellcheck ON attribute_spellcheck.attribute_code = attribute.code
            LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
            LEFT JOIN pim_data_quality_insights_attribute_group_activation AS activation ON activation.attribute_group_code = attribute_group.code
        WHERE family_attribute.family_id = family.id
            AND attribute_spellcheck.evaluated_at > family_evaluation.evaluated_at
            AND (activation.activated IS NULL OR activation.activated = 1) 
    );
SQL;
        $stmt = $this->dbConnection->executeQuery($query, ['statusDone' => CriterionEvaluationStatus::DONE]);

        $familyIds = [];
        $count = 0;
        while ($familyId = $stmt->fetchColumn()) {
            $familyIds[] = new FamilyId(intval($familyId));
            if(++$count % $bulkSize === 0) {
                yield $familyIds;
                $familyIds = [];
            }
        }

        if (!empty($familyIds)) {
            yield $familyIds;
        }
    }
}
