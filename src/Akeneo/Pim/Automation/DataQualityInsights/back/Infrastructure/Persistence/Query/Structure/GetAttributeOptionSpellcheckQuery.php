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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheckCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Doctrine\DBAL\Connection;

final class GetAttributeOptionSpellcheckQuery implements GetAttributeOptionSpellcheckQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    /** @var Clock */
    private $clock;

    public function __construct(Connection $dbConnection, Clock $clock)
    {
        $this->dbConnection = $dbConnection;
        $this->clock = $clock;
    }

    public function getByAttributeOptionCode(AttributeOptionCode $attributeOptionCode): ?AttributeOptionSpellcheck
    {
        $query = <<<SQL
SELECT evaluated_at, result, attribute_option_code
FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode AND attribute_option_code = :attributeOptionCode
SQL;

        $spellcheckData = $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeOptionCode->getAttributeCode(),
            'attributeOptionCode' => $attributeOptionCode,
        ])->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($spellcheckData)) {
            return null;
        }

        return $this->buildAttributeOptionSpellcheck($spellcheckData, $attributeOptionCode->getAttributeCode());
    }

    public function getByAttributeAndOptionCodes(AttributeCode $attributeCode, array $optionCodes): array
    {
        if (empty($optionCodes)) {
            return [];
        }

        $query = <<<SQL
SELECT evaluated_at, result, attribute_option_code
FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode AND attribute_option_code IN (:optionCodes)
SQL;

        $spellchecks = $this->dbConnection->executeQuery(
            $query,
            [
                'attributeCode' => strval($attributeCode),
                'optionCodes' => $optionCodes,
            ],
            [
                'attributeCode' => \PDO::PARAM_STR,
                'optionCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $results = [];
        while ($option = $spellchecks->fetch(\PDO::FETCH_ASSOC)) {
            $results[$option['attribute_option_code']] = $this->buildAttributeOptionSpellcheck($option, $attributeCode);
        }

        return $results;
    }

    public function evaluatedSince(\DateTimeImmutable $evaluatedSince): \Iterator
    {
        $query = <<<SQL
SELECT attribute_code, attribute_option_code, evaluated_at, result
FROM pimee_dqi_attribute_option_spellcheck
WHERE evaluated_at >= :evaluatedSince
SQL;
        $stmt = $this->dbConnection->executeQuery($query, [
            'evaluatedSince' => $evaluatedSince->format(Clock::TIME_FORMAT)
        ]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->buildAttributeOptionSpellcheck($row, new AttributeCode($row['attribute_code']));
        }
    }

    public function getByAttributeCodeWithSpellingMistakes(AttributeCode $attributeCode): array
    {
        $query = <<<SQL
SELECT evaluated_at, result, attribute_option_code
FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode AND to_improve = 1
SQL;

        $spellchecks = $this->dbConnection->executeQuery(
            $query,
            [
                'attributeCode' => strval($attributeCode),
            ],
            [
                'attributeCode' => \PDO::PARAM_STR,
            ]
        );

        $results = [];
        while ($option = $spellchecks->fetch(\PDO::FETCH_ASSOC)) {
            $results[$option['attribute_option_code']] = $this->buildAttributeOptionSpellcheck($option, $attributeCode);
        }

        return $results;
    }

    public function getByAttributeCode(AttributeCode $attributeCode): AttributeOptionSpellcheckCollection
    {
        $query = <<<SQL
SELECT attribute_option_code, evaluated_at, result
FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeCode,
        ]);

        $attributeOptionSpellchecks = new AttributeOptionSpellcheckCollection();
        while ($spellcheckData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $attributeOptionSpellchecks->add(
                $this->buildAttributeOptionSpellcheck($spellcheckData, $attributeCode)
            );
        }

        return $attributeOptionSpellchecks;
    }

    private function buildAttributeOptionSpellcheck(array $spellcheckData, AttributeCode $attributeCode): AttributeOptionSpellcheck
    {
        $spellcheckResult = new SpellcheckResultByLocaleCollection();
        foreach (json_decode($spellcheckData['result'], true) as $localeCode => $result) {
            $spellcheckResult->add(new LocaleCode($localeCode), new SpellCheckResult($result));
        }

        return new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, $spellcheckData['attribute_option_code']),
            $this->clock->fromString($spellcheckData['evaluated_at']),
            $spellcheckResult
        );
    }
}
