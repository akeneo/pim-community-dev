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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
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

        $this->dbConnection->executeQuery(
            $query,
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
        // Note 1: the query impact 2 things: it cleans spellcheck lines concerning an attribute option whose linked attribute
        // has been deleted (even if the attribute is recreated with the same code)
        // AND it cleans spellcheck lines concerning a deleted attribute option.
        // Note 2: Spellcheck table store codes of attribute and attribute_option. But 2 different "select attribute" can have
        // an "attribute option" with the same code (but different id). We make sure here that we clean spellcheck lines
        // linked to a deleted attribute by checking on the attribute_id
        $query = <<<SQL
DELETE spellcheck
FROM pimee_dqi_attribute_option_spellcheck AS spellcheck
LEFT JOIN pim_catalog_attribute AS attribute ON attribute.code = spellcheck.attribute_code
LEFT JOIN pim_catalog_attribute_option AS attribute_option ON (
    attribute_option.attribute_id=attribute.id
    AND attribute_option.code = spellcheck.attribute_option_code
)
WHERE attribute_option.id IS NULL
SQL;
        $this->dbConnection->executeQuery($query);
    }

    /**
     * Manages to clean DQI spellcheck table rows related to some attribute option(s)
     * @param string $attributeCode the code of the attribute whose option spellcheck rows are to be deleted
     * @param string|null $attributeOptionCode the code of the option to delete, if null then all rows will be deleted
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteUnknownAttributeOption(string $attributeCode, ?string $attributeOptionCode = null): void
    {
        $queryParts = [
            <<<SQL
DELETE spellcheck
FROM pimee_dqi_attribute_option_spellcheck AS spellcheck
LEFT JOIN pim_catalog_attribute_option AS attribute_option ON attribute_option.code = spellcheck.attribute_option_code
LEFT JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_option.attribute_id
WHERE spellcheck.attribute_code = :attributeCode
SQL
        ];
        $queryParameters = ['attributeCode' => $attributeCode];

        if ($attributeOptionCode != null) {
            $queryParts[] = 'AND spellcheck.attribute_option_code = :attributeOptionCode';
            $queryParameters['attributeOptionCode'] = $attributeOptionCode;
        }

        $queryParts[] = 'AND attribute_option.id IS NULL OR attribute.id IS NULL';

        $query = join("\n", $queryParts);

        $this->dbConnection->executeQuery($query, $queryParameters);
    }

    private function formatSpellcheckResult(SpellcheckResultByLocaleCollection $result): string
    {
        return json_encode($result->toArrayBool());
    }
}
