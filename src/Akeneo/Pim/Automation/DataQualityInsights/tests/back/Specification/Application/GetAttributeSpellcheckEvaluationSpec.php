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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeOptionsSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;

class GetAttributeSpellcheckEvaluationSpec extends ObjectBehavior
{
    public function let(
        GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery,
        GetAttributeSpellcheckQueryInterface $attributeSpellcheckQuery
    )
    {
        $this->beConstructedWith(
            $attributeOptionsSpellcheckQuery,
            $attributeSpellcheckQuery
        );
    }

    public function it_returns_formatted_evaluation(
        $attributeOptionsSpellcheckQuery,
        $attributeSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('attribute_processed');
        $attributeOptionsSpellcheckEvaluation = $this->givenAttributeOptionsSpellcheckEvaluation($attributeCode);
        $attributeSpellcheckEvaluation = $this->givenAttributeSpellcheckEvaluation($attributeCode);

        $attributeOptionsSpellcheckQuery->byAttributeCode($attributeCode, 10000, null)->willReturn($attributeOptionsSpellcheckEvaluation);
        $attributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn($attributeSpellcheckEvaluation);

        $this->get($attributeCode)->shouldBeLike([
            'attribute' =>'attribute_processed',
            'options_count' => 4,
            'options' => [
                '1' => [
                    'toImprove' => 3,
                    'locales' => ['de_DE' => true, 'en_US' => true, 'fr_FR' => true],
                ],
                'option2' => [
                    'toImprove' => 1,
                    'locales' => ['de_DE' => false, 'en_US' => false, 'fr_FR' => true],
                ],
                '3' => [
                    'toImprove' => 0,
                    'locales' => ['de_DE' => false, 'en_US' => false, 'fr_FR' => false],
                ],
            ],
            'labels_count' => 2,
            'labels' => [
                'de_DE' => true,
                'en_US' => true,
                'fr_FR' => false,
            ],
        ]);
    }

    public function it_returns_formatted_evaluation_using_search_after_query(
        $attributeOptionsSpellcheckQuery,
        $attributeSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('attribute_processed');
        $attributeOptionsSpellcheckEvaluation = [];
        foreach (range(1, 10002) as $option) {
            $attributeOptionsSpellcheckEvaluation[] = new AttributeOptionSpellcheck(
                new AttributeOptionCode($attributeCode, (string) $option),
                new \DateTimeImmutable('2020-06-10 10:00:00'),
                (new SpellcheckResultByLocaleCollection())
                    ->add(new LocaleCode('de_DE'), new SpellCheckResult(true))
                    ->add(new LocaleCode('en_US'), new SpellCheckResult(true))
                    ->add(new LocaleCode('fr_FR'), new SpellCheckResult(false))
            );
        }
        $attributeOptionsSpellcheckEvaluationChunked = array_chunk($attributeOptionsSpellcheckEvaluation, 10000);
        $attributeSpellcheckEvaluation = $this->givenAttributeSpellcheckEvaluation($attributeCode);

        $attributeOptionsSpellcheckQuery->byAttributeCode($attributeCode, 10000, null)
            ->willReturn($attributeOptionsSpellcheckEvaluationChunked[0]);
        $attributeOptionsSpellcheckQuery->byAttributeCode($attributeCode, 10000, '10000')
            ->willReturn($attributeOptionsSpellcheckEvaluationChunked[1]);
        $attributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn($attributeSpellcheckEvaluation);

        $result = $this->get($attributeCode);
        $result['options_count']->shouldBe(20004);
        $result['options']->shouldHaveCount(10002);
    }

    public function it_returns_formatted_evaluation_when_spelling_has_not_been_calculated(
        $attributeOptionsSpellcheckQuery,
        $attributeSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('attribute_not_processed');

        $attributeOptionsSpellcheckQuery->byAttributeCode($attributeCode, 10000, null)->willReturn([]);
        $attributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(null);

        $this->get($attributeCode)->shouldBeLike([
            'attribute' =>'attribute_not_processed',
            'options_count' => 0,
            'options' => [],
            'labels_count' => 0,
            'labels' => [],
        ]);
    }

    private function givenAttributeOptionsSpellcheckEvaluation(AttributeCode $attributeCode)
    {
        $option1 = new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, '1'),
            new \DateTimeImmutable('2020-06-10 10:00:00'),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('de_DE'), new SpellCheckResult(true))
                ->add(new LocaleCode('en_US'), new SpellCheckResult(true))
                ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true))
        );

        $option2 = new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, 'option2'),
            new \DateTimeImmutable('2020-06-10 10:00:00'),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('de_DE'), new SpellCheckResult(false))
                ->add(new LocaleCode('en_US'), new SpellCheckResult(false))
                ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true))
        );

        $option3 = new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, '3'),
            new \DateTimeImmutable('2020-06-10 10:00:00'),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('de_DE'), new SpellCheckResult(false))
                ->add(new LocaleCode('en_US'), new SpellCheckResult(false))
                ->add(new LocaleCode('fr_FR'), new SpellCheckResult(false))
        );

        return [
            $option1,
            $option2,
            $option3,
        ];
    }

    private function givenAttributeSpellcheckEvaluation(AttributeCode $attributeCode)
    {
        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('de_DE'), new SpellCheckResult(true))
                ->add(new LocaleCode('en_US'), new SpellCheckResult(true))
                ->add(new LocaleCode('fr_FR'), new SpellCheckResult(false))
        );
    }
}
