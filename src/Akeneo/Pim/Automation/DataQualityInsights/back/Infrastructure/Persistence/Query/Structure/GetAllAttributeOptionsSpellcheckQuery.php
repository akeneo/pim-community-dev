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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeOptionsSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Doctrine\DBAL\Connection;

class GetAllAttributeOptionsSpellcheckQuery implements GetAllAttributeOptionsSpellcheckQueryInterface
{
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byAttributeCode(
        AttributeCode $attributeCode,
        int $limit = 0,
        ?string $searchAfterOptionCode = null
    ): array {
        $query = <<<SQL
SELECT attribute_code, attribute_option_code, evaluated_at, result
FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode {searchAfterCondition}
ORDER BY attribute_code, attribute_option_code
{limit}
;
SQL;
        $queryParams = ['attributeCode' => strval($attributeCode)];
        $searchAfterCondition = '';
        if ($searchAfterOptionCode) {
            $searchAfterCondition = "AND attribute_option_code > :searchAfter";
            $queryParams = ['attributeCode' => strval($attributeCode), 'searchAfter' => $searchAfterOptionCode];
        }
        $query = strtr($query, [
            '{limit}' => $limit > 0 ? sprintf('LIMIT %d', $limit) : '',
            '{searchAfterCondition}' => $searchAfterCondition,
        ]);

        return $this->dbConnection
            ->executeQuery($query, $queryParams)
            ->fetchAll(\PDO::FETCH_FUNC, [$this, 'format']);
    }

    public function format($attribute_code, $attribute_option_code, $evaluated_at, $result)
    {
        $attributeCode = new AttributeCode($attribute_code);
        $decodedResult = json_decode((string) $result, true);

        if (!is_array($decodedResult)) {
            $decodedResult = [];
        }

        $evaluationResult = new SpellcheckResultByLocaleCollection();
        foreach ($decodedResult as $localeCode => $localeResult) {
            $evaluationResult->add(new LocaleCode($localeCode), new SpellCheckResult($localeResult));
        }

        return new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, $attribute_option_code),
            new \DateTimeImmutable($evaluated_at),
            $evaluationResult
        );
    }
}
