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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Doctrine\DBAL\Connection;

final class GetAttributeSpellcheckQuery implements GetAttributeSpellcheckQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getByAttributeCode(AttributeCode $attributeCode): ?AttributeSpellcheck
    {
        $result = $this->getByAttributeCodes([$attributeCode]);
        if (empty($result) || !array_key_exists(strval($attributeCode), $result)) {
            return null;
        }

        return $result[strval($attributeCode)];
    }

    public function getByAttributeCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $attributeCodes = array_map(function (AttributeCode $attributeCode) {
            return strval($attributeCode);
        }, $attributeCodes);

        $query = <<<SQL
SELECT spellcheck.result, spellcheck.evaluated_at, spellcheck.attribute_code
FROM pimee_dqi_attribute_spellcheck AS spellcheck
WHERE spellcheck.attribute_code IN(:attributeCodes)
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );

        $attributeSpellChecks = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $attributeSpellChecks[$row['attribute_code']] = $this->buildAttributeSpellcheck(
                new AttributeCode($row['attribute_code']),
                $row['evaluated_at'],
                $row['result']
            );
        }

        return $attributeSpellChecks;
    }

    private function buildAttributeSpellcheck(AttributeCode $attributeCode, string $evaluationDate, string $resultAsJson): AttributeSpellcheck
    {
        $evaluationResult = new SpellcheckResultByLocaleCollection();
        foreach (json_decode($resultAsJson, true) as $localeCode => $result) {
            $evaluationResult->add(new LocaleCode($localeCode), new SpellCheckResult($result));
        }

        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable($evaluationDate),
            $evaluationResult
        );
    }
}
