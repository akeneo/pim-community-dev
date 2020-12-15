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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeOptionCodesToEvaluateQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

class GetAttributeOptionCodesToEvaluateQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_codes_of_the_attribute_options_that_need_to_be_evaluated()
    {
        $now = new \DateTimeImmutable();
        $this->givenASimpleSelectAttribute('color');
        $this->givenASimpleSelectAttribute('brand');

        $this->givenAnAttributeOptionWithAnUpToDateEvaluation('color', 'blue', $now->modify('-10 minutes'));
        $this->givenAnAttributeOptionWithAnUpToDateEvaluation('color', 'yellow', $now);
        $this->givenAnUpdatedAttributeOptionWithoutUpToDateEvaluation('color', 'red', $now);
        $this->givenAnUpdatedAttributeOptionWithoutUpToDateEvaluation('brand', 'foo', $now);

        $expectedAttributeOptions = [
             new AttributeOptionCode(new AttributeCode('color'), 'yellow'),
             new AttributeOptionCode(new AttributeCode('color'), 'red'),
             new AttributeOptionCode(new AttributeCode('brand'), 'foo'),
        ];

        $attributeOptionsToEvaluate = $this->get(GetAttributeOptionCodesToEvaluateQuery::class)->execute($now->modify('-1 minute'));

        $this->assertEqualsCanonicalizing($expectedAttributeOptions, iterator_to_array($attributeOptionsToEvaluate));
    }

    private function givenAnAttributeOptionWithAnUpToDateEvaluation(string $attributeCode, string $optionCode, \DateTimeImmutable $now): void
    {
        $attributeOption = $this->createAttributeOption($attributeCode, $optionCode);
        $this->attributeOptionVersioningLoggedAt($attributeOption->getId(), $now);
        $this->createAttributeOptionSpellcheck($attributeCode, $optionCode, $now);
    }

    private function givenAnUpdatedAttributeOptionWithoutUpToDateEvaluation(string $attributeCode, string $optionCode, \DateTimeImmutable $now): void
    {
        $attributeOption = $this->createAttributeOption($attributeCode, $optionCode);
        $this->updateAttributeOption($attributeOption, ['labels' => ['en_US' => 'Updated ' . $optionCode]]);
        $this->attributeOptionVersioningLoggedAt($attributeOption->getId(), $now);

        $this->createAttributeOptionSpellcheck($attributeCode, $optionCode, $now->modify('-1 second'));
    }

    private function givenASimpleSelectAttribute(string $attributeCode): void
    {
        $this->createAttribute($attributeCode, ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
    }

    private function createAttributeOption(string $attributeCode, string $optionCode): AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->updateAttributeOption($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => [
                'en_US' => strtoupper($optionCode)
            ],
        ]);

        return $attributeOption;
    }

    private function updateAttributeOption(AttributeOptionInterface $attributeOption, array $data): void
    {
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    private function createAttributeOptionSpellcheck(string $attributeCode, string $optionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $this->get(AttributeOptionSpellcheckRepository::class)->save(
            new AttributeOptionSpellcheck(
                new AttributeOptionCode(new AttributeCode($attributeCode), $optionCode),
                $evaluatedAt,
                new SpellcheckResultByLocaleCollection()
            )
        );
    }

    private function attributeOptionVersioningLoggedAt(int $attributeOptionId, \DateTimeImmutable $loggedAt): void
    {
        $query = <<<SQL
UPDATE pim_versioning_version SET logged_at = :loggedAt
WHERE resource_name = :resourceName AND resource_id = :resourceId
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'resourceName' => $this->getParameter('pim_catalog.entity.attribute_option.class'),
            'resourceId' => $attributeOptionId,
            'loggedAt' => $loggedAt->format('Y-m-d H:i:s')
        ]);
    }
}
