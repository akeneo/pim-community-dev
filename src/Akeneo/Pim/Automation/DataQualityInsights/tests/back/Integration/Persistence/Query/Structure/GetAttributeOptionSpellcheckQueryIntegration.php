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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeOptionSpellcheckQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Test\Integration\TestCase;

final class GetAttributeOptionSpellcheckQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_spellcheck_of_a_given_attribute_option()
    {
        $this->createAttributeOptionSpellcheck('color', 'blue');
        $this->createAttributeOptionSpellcheck('secondary_color', 'red');

        $expectedSpellcheck = $this->createAttributeOptionSpellcheck('color', 'red', (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::good())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove()));

        $attributeOptionCode = new AttributeOptionCode(new AttributeCode('color'), 'red');
        $spellcheck = $this->get(GetAttributeOptionSpellcheckQuery::class)->getByAttributeOptionCode($attributeOptionCode);

        $this->assertEquals($expectedSpellcheck, $spellcheck);
    }

    public function test_it_returns_the_spellchecks_of_multiple_attribute_options()
    {
        $expectedSpellchecks = [];
        $this->createAttributeOptionSpellcheck('color', 'blue');
        $this->createAttributeOptionSpellcheck('color', 'yellow');
        $expectedSpellchecks['red'] = $this->createAttributeOptionSpellcheck('secondary_color', 'red');
        $expectedSpellchecks['blue'] = $this->createAttributeOptionSpellcheck('secondary_color', 'blue');
        $expectedSpellchecks['black'] = $this->createAttributeOptionSpellcheck('secondary_color', 'black');
        $this->createAttributeOptionSpellcheck('secondary_color', 'white');

        $spellchecks = $this->get(GetAttributeOptionSpellcheckQuery::class)->getByAttributeAndOptionCodes(
            new AttributeCode('secondary_color'),
            [
                'red',
                'blue',
                'black'
            ]
        );

        $this->assertEqualsCanonicalizing(
            sort($expectedSpellchecks),
            sort($spellchecks)
        );
    }

    public function test_it_returns_the_spellchecks_evaluated_since_a_given_date()
    {
        $evaluatedSince = $this->get(SystemClock::class)->fromString('2020-05-12 11:23:45');

        $this->createAttributeOptionSpellcheck('color', 'red', null, $evaluatedSince->modify('-1 SECOND'));
        $expectedSpellchecks = [
            $this->createAttributeOptionSpellcheck('color', 'blue', null, $evaluatedSince),
            $this->createAttributeOptionSpellcheck('material', 'wood', null, $evaluatedSince->modify('+3 SECOND'))
        ];

        $attributeOptionCodes = iterator_to_array(
            $this->get(GetAttributeOptionSpellcheckQuery::class)->evaluatedSince($evaluatedSince)
        );

        $this->assertEquals($expectedSpellchecks, $attributeOptionCodes);
    }

    public function test_it_returns_evaluations_with_spelling_mistakes_by_attribute_code()
    {
        $this->createAttributeOptionSpellcheck('color', 'blue');
        $this->createAttributeOptionSpellcheck('secondary_color', 'yellow');

        $expectedSpellchecks = [];
        $expectedSpellchecks['red'] = $this->createAttributeOptionSpellcheck('secondary_color', 'red', (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::good())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove()));
        $this->createAttributeOptionSpellcheck('secondary_color', 'blue', (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::good())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::good()));
        $expectedSpellchecks['black'] = $this->createAttributeOptionSpellcheck('secondary_color', 'black', (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove()));

        $spellchecks = $this->get(GetAttributeOptionSpellcheckQuery::class)->getByAttributeCodeWithSpellingMistakes(
            new AttributeCode('secondary_color')
        );

        $this->assertEquals($expectedSpellchecks, $spellchecks);
    }

    public function test_it_returns_the_attribute_options_spellchecks_for_a_given_attribute()
    {
        $this->createAttributeOptionSpellcheck('secondary_color', 'red');

        $expectedSpellchecks[] = $this->createAttributeOptionSpellcheck('color', 'red', (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::good())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove()));

        $expectedSpellchecks[] = $this->createAttributeOptionSpellcheck('color', 'blue', new SpellcheckResultByLocaleCollection());

        $attributeOptionsSpellchecks = $this->get(GetAttributeOptionSpellcheckQuery::class)
            ->getByAttributeCode(new AttributeCode('color'));

        $this->assertEqualsCanonicalizing($expectedSpellchecks, $attributeOptionsSpellchecks->toArray());
    }

    private function createAttributeOptionSpellcheck(
        string $attributeCode,
        string $optionCode,
        ?SpellcheckResultByLocaleCollection $result = null,
        ?\DateTimeImmutable $evaluatedAt = null
    ): AttributeOptionSpellcheck {
        $attributeOptionSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attributeCode), $optionCode),
            $evaluatedAt ?? $this->get(SystemClock::class)->fromString('2020-05-12 11:23:45'),
            $result ?? new SpellcheckResultByLocaleCollection()
        );

        $this->get(AttributeOptionSpellcheckRepository::class)->save($attributeOptionSpellcheck);

        return $attributeOptionSpellcheck;
    }
}
