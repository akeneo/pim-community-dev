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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeSpellcheckRepositoryInterface;
use Doctrine\DBAL\Connection;

class AttributeSpellcheckRepository implements AttributeSpellcheckRepositoryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function save(AttributeSpellcheck $attributeSpellcheck): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_attribute_spellcheck (attribute_code, evaluated_at, to_improve, result)
VALUES (:attributeCode, :evaluatedAt, :toImprove, :result)
ON DUPLICATE KEY UPDATE evaluated_at = :evaluatedAt, to_improve = :toImprove, result = :result;
SQL;

        $this->dbConnection->executeQuery(
            $query,
            [
                'attributeCode' => $attributeSpellcheck->getAttributeCode(),
                'evaluatedAt' => $attributeSpellcheck->getEvaluatedAt()->format(Clock::TIME_FORMAT),
                'toImprove' => $attributeSpellcheck->getResult()->isToImprove(),
                'result' => $this->formatSpellcheckResult($attributeSpellcheck->getResult())
            ],
            [
                'toImprove' => \PDO::PARAM_BOOL
            ]
        );
    }

    public function deleteUnknownAttributes(): void
    {
        $query = <<<SQL
DELETE spellcheck
FROM pimee_dqi_attribute_spellcheck AS spellcheck
LEFT JOIN pim_catalog_attribute AS attribute ON attribute.code = spellcheck.attribute_code
WHERE attribute.code IS NULL;
SQL;

        $this->dbConnection->executeQuery($query);
    }

    public function delete(string $attributeCode): void
    {
        $query =
            <<<SQL
DELETE spellcheck
FROM pimee_dqi_attribute_spellcheck AS spellcheck
WHERE spellcheck.attribute_code = :attribute_code
SQL;
        $queryParameters = ['attribute_code' => $attributeCode];
        $this->dbConnection->executeQuery($query, $queryParameters);
    }

    private function formatSpellcheckResult(SpellcheckResultByLocaleCollection $result): string
    {
        return json_encode($result->toArrayBool());
    }
}
