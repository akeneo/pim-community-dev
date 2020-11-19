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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;

final class AttributeOptionSpellcheckCollectionSpec extends ObjectBehavior
{
    public function it_determines_if_the_collection_is_empty()
    {
        $this->isEmpty()->shouldReturn(true);

        $this->add($this->givenAnAttributeOptionSpellcheckNotApplicable('color', 'white'));
        $this->isEmpty()->shouldReturn(false);
    }

    public function it_determines_if_the_collection_is_empty_for_a_given_locale()
    {
        $enUS = new LocaleCode('en_US');
        $frFR = new LocaleCode('fr_FR');

        $this->isEmptyForLocale($enUS)->shouldReturn(true);

        $this->add($this->givenAnAttributeOptionSpellcheckGoodInEnglish('color', 'white'));

        $this->isEmptyForLocale($enUS)->shouldReturn(false);
        $this->isEmptyForLocale($frFR)->shouldReturn(true);
    }

    public function it_determines_if_there_is_an_attribute_option_spellcheck_to_improve_in_the_collection()
    {
        $this->hasAttributeOptionToImprove()->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckGoodInEnglish('color', 'red'));
        $this->hasAttributeOptionToImprove()->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckNotApplicable('color', 'white'));
        $this->hasAttributeOptionToImprove()->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckToImproveInFrenchAndGoodInEnglish('color', 'blue'));
        $this->hasAttributeOptionToImprove()->shouldReturn(true);
    }

    public function it_determines_if_there_is_an_attribute_option_spellcheck_to_improve_for_a_given_locale()
    {
        $enUS = new LocaleCode('en_US');
        $frFR = new LocaleCode('fr_FR');

        $this->hasAttributeOptionToImproveForLocale($enUS)->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckNotApplicable('color', 'white'));
        $this->hasAttributeOptionToImproveForLocale($enUS)->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckToImproveInFrenchAndGoodInEnglish('color', 'blue'));
        $this->hasAttributeOptionToImproveForLocale($enUS)->shouldReturn(false);
        $this->hasAttributeOptionToImproveForLocale($frFR)->shouldReturn(true);
    }

    public function it_determines_if_all_attribute_option_spellchecks_are_good()
    {
        $this->hasOnlyGoodSpellchecks()->shouldReturn(false);

        $this->add($this->givenAnAttributeOptionSpellcheckGoodInEnglish('color', 'red'));
        $this->hasOnlyGoodSpellchecks()->shouldReturn(true);

        $this->add($this->givenAnAttributeOptionSpellcheckGoodInEnglish('color', 'blue'));
        $this->hasOnlyGoodSpellchecks()->shouldReturn(true);

        $this->add($this->givenAnAttributeOptionSpellcheckNotApplicable('color', 'red'));
        $this->hasOnlyGoodSpellchecks()->shouldReturn(false);
    }

    private function givenAnAttributeOptionSpellcheckToImproveInFrenchAndGoodInEnglish(string $attribute, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attribute), $option),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );
    }

    private function givenAnAttributeOptionSpellcheckGoodInEnglish(string $attribute, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attribute), $option),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        );
    }

    private function givenAnAttributeOptionSpellcheckNotApplicable(string $attribute, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attribute), $option),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );
    }
}
