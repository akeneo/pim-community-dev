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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributesCodesToEvaluateQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;

class GetAttributesCodesToEvaluateQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_codes_of_the_attributes_that_need_to_be_evaluated()
    {
        $this->givenAnAttributeThatHasAlreadyBeenEvaluated('height');
        $this->givenANewlyCreatedAttribute('title');
        $this->givenAnUpdatedAttribute('name');
        $this->givenAnUpdatedAttribute('description');
        $this->createAttributeEvaluation('sku', new \DateTimeImmutable());

        $expectedAttributesCodes = [
            new AttributeCode('name'),
            new AttributeCode('description'),
            new AttributeCode('title'),
        ];

        $attributesToEvaluate = iterator_to_array($this->get(GetAttributesCodesToEvaluateQuery::class)->execute());

        $this->assertEqualsCanonicalizing($expectedAttributesCodes, $attributesToEvaluate);
    }

    private function givenAnAttributeThatHasAlreadyBeenEvaluated(string $code): void
    {
        $createdAt = new \DateTimeImmutable('2020-04-12 09:12:43');
        $this->createAttribute($code);
        $this->attributeUpdatedAt($code, $createdAt);
        $this->createAttributeEvaluation($code, $createdAt);
    }

    private function givenANewlyCreatedAttribute(string $code): void
    {
        $this->createAttribute($code);
    }

    private function givenAnUpdatedAttribute(string $code): void
    {
        $createdAt = new \DateTimeImmutable('2020-04-12 09:12:43');
        $this->createAttribute($code);
        $this->createAttributeEvaluation($code, $createdAt);
        $this->attributeUpdatedAt($code, $createdAt->modify('+1 SECOND'));
    }

    private function createAttribute(string $code): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createAttributeEvaluation(string $attributeCode, \DateTimeImmutable $evaluatedAt): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            $evaluatedAt,
            new SpellcheckResultByLocaleCollection()
        ));
    }

    private function attributeUpdatedAt(string $attributeCode, \DateTimeImmutable $updatedAt): void
    {
        $query = <<<SQL
UPDATE pim_catalog_attribute SET updated = :updatedAt WHERE code = :attributeCode
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'updatedAt' => $updatedAt->format(Clock::TIME_FORMAT),
            'attributeCode' => $attributeCode
        ]);
    }
}
