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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributesCodesToEvaluateQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Webmozart\Assert\Assert;

class GetAttributesCodesToEvaluateQueryIntegration extends DataQualityInsightsTestCase
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
        ];

        $attributesToEvaluate = iterator_to_array($this->get(GetAttributesCodesToEvaluateQuery::class)->execute());

        $this->assertEqualsCanonicalizing($expectedAttributesCodes, $attributesToEvaluate);
    }

    public function test_it_returns_the_codes_of_the_attributes_that_need_to_be_reevaluated()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);

        $size = $this->createAttribute('size', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('size', ['S', 'M', 'L']);

        $this->updateAttribute($size, [
            'labels' => [
                'en_US' => 'Size',
                'fr_FR' => 'Taille'
            ]
        ]);

        $this->createAttributeOptionSpellcheck(
            'size',
            'S',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        );

        $this->createAttributeOptionSpellcheck(
            'size',
            'M',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        );

        $this->createAttributeOptionSpellcheck(
            'size',
            'L',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        );

        $this->setGlobalQualityToImprove('size');
        $this->setLocalesQualityToImprove('size');
        $this->setOptionSpellcheckToGood('size', 's');

        $attributesToEvaluate = iterator_to_array($this->get(GetAttributesCodesToEvaluateQuery::class)->toReevaluate());

        $this->assertEqualsCanonicalizing(['size'], $attributesToEvaluate);
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

    private function setGlobalQualityToImprove(string $attributeCode): void
    {
        $this->get('database_connection')->executeQuery(
            <<<SQL
UPDATE pimee_dqi_attribute_quality
SET quality = 'to_improve'
WHERE attribute_code = :attributeCode;
SQL,
            ['attributeCode' => $attributeCode]
        );
    }

    private function setLocalesQualityToImprove(string $attributeCode): void
    {
        $this->get('database_connection')->executeQuery(
            <<<SQL
UPDATE pimee_dqi_attribute_locale_quality
SET quality = 'to_improve'
WHERE attribute_code = :attributeCode;
SQL,
            ['attributeCode' => $attributeCode]
        );
    }

    private function setOptionSpellcheckToGood(string $attributeCode, string $optionCode): void
    {
        $query = <<<SQL
UPDATE pimee_dqi_attribute_option_spellcheck
SET to_improve = 0
WHERE attribute_code = :attributeCode AND attribute_option_code = :attributeOptionCode;
SQL;
        $this->get('database_connection')->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'attributeOptionCode' => $optionCode,
        ]);
    }

    private function updateAttribute(AttributeInterface $attribute, array $data): void
    {
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $errors = $this->get('validator')->validate($attribute);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOptionSpellcheck(string $attributeCode, string $optionCode, ?SpellcheckResultByLocaleCollection $result = null)
    {
        $attributeOptionSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attributeCode), $optionCode),
            $this->get(SystemClock::class)->fromString('2020-05-12 11:23:45'),
            $result ?? new SpellcheckResultByLocaleCollection()
        );

        $this->get(AttributeOptionSpellcheckRepository::class)->save($attributeOptionSpellcheck);
    }
}
