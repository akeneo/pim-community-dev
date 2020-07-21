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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeSpellcheckQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Test\Integration\TestCase;

class GetAttributeSpellcheckQueryIntegration extends TestCase
{
    public function test_it_returns_the_spellcheck_result_of_an_attribute()
    {
        $heightAttributeSpellcheck = $this->createHeightAttributeEvaluation();
        $widthAttributeSpellcheck = $this->createWidthAttributeEvaluation();

        $attributeSpellcheck = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCode(new AttributeCode('height'));
        $this->assertEquals($heightAttributeSpellcheck, $attributeSpellcheck);

        $attributeSpellcheck = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCode(new AttributeCode('width'));
        $this->assertEquals($widthAttributeSpellcheck, $attributeSpellcheck);

        $attributeSpellcheck = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCode(new AttributeCode('size'));
        $this->assertNull($attributeSpellcheck);
    }

    public function test_it_returns_the_spellcheck_results_of_a_collection_of_attributes()
    {
        $heightAttributeSpellcheck = $this->createHeightAttributeEvaluation();
        $widthAttributeSpellcheck = $this->createWidthAttributeEvaluation();

        $result = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCodes([]);
        $this->assertEmpty($result);

        $result = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCodes([new AttributeCode('height'), new AttributeCode('width')]);
        $this->assertEquals(
            [
                'height' => $heightAttributeSpellcheck,
                'width' => $widthAttributeSpellcheck,
            ],
            $result
        );

        $result = $this->get(GetAttributeSpellcheckQuery::class)->getByAttributeCodes([new AttributeCode('height'), new AttributeCode('width'), new AttributeCode('unknown_attribute_code')]);
        $this->assertEquals(
            [
                'height' => $heightAttributeSpellcheck,
                'width' => $widthAttributeSpellcheck,
            ],
            $result
        );
    }

    private function createWidthAttributeEvaluation()
    {
        $widthAttributeEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_EN'), new SpellCheckResult(true))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(false))
            ->add(new LocaleCode('de_DE'), new SpellCheckResult(true));

        $widthAttributeCode = new AttributeCode('width');

        $widthAttributeSpellcheck = new AttributeSpellcheck(
            $widthAttributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            $widthAttributeEvaluationResult
        );


        $this->createAttributeEvaluation($widthAttributeCode, new \DateTimeImmutable('2020-04-12 09:12:43'), $widthAttributeEvaluationResult);

        return $widthAttributeSpellcheck;
    }

    private function createHeightAttributeEvaluation()
    {
        $heightAttributeCode = new AttributeCode('height');

        $heightAttributeEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_EN'), new SpellCheckResult(false))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true))
            ->add(new LocaleCode('de_DE'), new SpellCheckResult(false));

        $heightAttributeSpellcheck = new AttributeSpellcheck(
            $heightAttributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            $heightAttributeEvaluationResult
        );

        $this->createAttributeEvaluation($heightAttributeCode, new \DateTimeImmutable('2020-04-12 09:12:43'), $heightAttributeEvaluationResult);

        return $heightAttributeSpellcheck;
    }

    private function createAttributeEvaluation(AttributeCode $attributeCode, \DateTimeImmutable $evaluatedAt, SpellcheckResultByLocaleCollection $evaluation): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            $attributeCode,
            $evaluatedAt,
            $evaluation
        ));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
