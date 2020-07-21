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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeOptionSpellcheckRepositoryInterface;
use Doctrine\DBAL\Connection;

class AttributeOptionSpellcheckRepository implements AttributeOptionSpellcheckRepositoryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function save(AttributeOptionSpellcheck $attributeOptionSpellcheck): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_attribute_option_spellcheck (attribute_code, attribute_option_code, evaluated_at, to_improve, result) 
VALUES (:attributeCode, :attributeOptionCode, :evaluatedAt, :toImprove, :result)
ON DUPLICATE KEY UPDATE evaluated_at = :evaluatedAt, to_improve = :toImprove, result = :result;
SQL;

        $this->dbConnection->executeQuery($query,
            [
                'attributeCode' => $attributeOptionSpellcheck->getAttributeCode(),
                'attributeOptionCode' => $attributeOptionSpellcheck->getAttributeOptionCode(),
                'evaluatedAt' => $attributeOptionSpellcheck->getEvaluatedAt()->format(Clock::TIME_FORMAT),
                'toImprove' => $attributeOptionSpellcheck->getResult()->isToImprove(),
                'result' => $this->formatSpellcheckResult($attributeOptionSpellcheck->getResult())
            ],
            [
                'toImprove' => \PDO::PARAM_BOOL
            ]
        );
    }

    public function deleteUnknownAttributeOptions(): void
    {
        $query = <<<SQL
DELETE spellcheck
FROM pimee_dqi_attribute_option_spellcheck AS spellcheck
LEFT JOIN pim_catalog_attribute_option AS attribute_option ON attribute_option.code = spellcheck.attribute_option_code
LEFT JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_option.attribute_id
WHERE attribute_option.id IS NULL OR attribute.id IS NULL
SQL;
        $this->dbConnection->executeQuery($query);
    }

    private function formatSpellcheckResult(SpellcheckResultByLocaleCollection $result): string
    {
        return json_encode($result->toArrayBool());
    }
}
