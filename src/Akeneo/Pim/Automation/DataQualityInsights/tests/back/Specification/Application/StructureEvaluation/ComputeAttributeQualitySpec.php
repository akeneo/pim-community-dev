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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\ComputeAttributeQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheckCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;


/*
 * Matrix of expected quality (global or for a locale)
 *
 *  Attribute spellcheck | Options Spellcheck | Quality
 * ----------------------|--------------------|------------
 *  not evaluated        | good               | processing
 *  not evaluated        | to_improve         | processing
 *  not evaluated        | n_a                | processing
 *  good                 | good               | good
 *  good                 | to_improve         | to_improve
 *  good                 | n_a                | good
 *  to_improve           | good               | to_improve
 *  to_improve           | to_improve         | to_improve
 *  to_improve           | n_a                | to_improve
 *  n_a                  | good               | good
 *  n_a                  | to_improve         | to_improve
 *  n_a                  | n_a                | n_a
 */
final class ComputeAttributeQualitySpec extends ObjectBehavior
{
    public function it_computes_the_global_quality_of_an_attribute()
    {
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            null,
            $this->givenAttributeOptionsSpellcheckGood()
        ), Quality::processing());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            null,
            $this->givenAttributeOptionsSpellcheckToImprove()
        ), Quality::processing());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            null,
            $this->givenAttributeOptionsSpellcheckNotApplicable()
        ), Quality::processing());

        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckGood(),
            $this->givenAttributeOptionsSpellcheckGood()
        ), Quality::good());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckGood(),
            $this->givenAttributeOptionsSpellcheckNotApplicable()
        ), Quality::good());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckNotApplicable(),
            $this->givenAttributeOptionsSpellcheckGood()
        ), Quality::good());

        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckGood(),
            $this->givenAttributeOptionsSpellcheckToImprove()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckToImprove(),
            $this->givenAttributeOptionsSpellcheckGood()
        ), Quality::toImprove());
         Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckToImprove(),
            $this->givenAttributeOptionsSpellcheckToImprove()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckToImprove(),
            $this->givenAttributeOptionsSpellcheckNotApplicable()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckNotApplicable(),
            $this->givenAttributeOptionsSpellcheckToImprove()
        ), Quality::toImprove());

        Assert::eq(ComputeAttributeQuality::computeGlobalQuality(
            $this->givenAttributeSpellcheckNotApplicable(),
            $this->givenAttributeOptionsSpellcheckNotApplicable()
        ), Quality::notApplicable());
    }

    public function it_computes_the_quality_of_an_attribute_for_a_given_locale()
    {
        $enUS = new LocaleCode('en_US');

        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            null,
            $this->givenAttributeOptionsSpellcheckGoodInEnglishAnToImproveInFrench()
        ), Quality::processing());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            null,
            $this->givenAttributeOptionsSpellcheckToImproveInEnglishAndGoodInFrench()
        ), Quality::processing());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            null,
            $this->givenAttributeOptionsSpellcheckNotApplicableInEnglish()
        ), Quality::processing());

        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckGoodInEnglishAnToImproveInFrench(),
            $this->givenAttributeOptionsSpellcheckGoodInEnglishAnToImproveInFrench()
        ), Quality::good());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckGoodInEnglishAnToImproveInFrench(),
            $this->givenAttributeOptionsSpellcheckNotApplicableInEnglish()
        ), Quality::good());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckNotApplicableInEnglish(),
            $this->givenAttributeOptionsSpellcheckGoodInEnglishAnToImproveInFrench()
        ), Quality::good());

        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckGoodInEnglishAnToImproveInFrench(),
            $this->givenAttributeOptionsSpellcheckToImproveInEnglishAndGoodInFrench()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckToImproveInEnglishAndGoodInFrench(),
            $this->givenAttributeOptionsSpellcheckGoodInEnglishAnToImproveInFrench()
        ), Quality::toImprove());
         Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckToImproveInEnglishAndGoodInFrench(),
            $this->givenAttributeOptionsSpellcheckToImproveInEnglishAndGoodInFrench()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckToImproveInEnglishAndGoodInFrench(),
            $this->givenAttributeOptionsSpellcheckNotApplicableInEnglish()
        ), Quality::toImprove());
        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckNotApplicableInEnglish(),
            $this->givenAttributeOptionsSpellcheckToImproveInEnglishAndGoodInFrench()
        ), Quality::toImprove());

        Assert::eq(ComputeAttributeQuality::computeLocaleQuality(
            $enUS,
            $this->givenAttributeSpellcheckNotApplicableInEnglish(),
            $this->givenAttributeOptionsSpellcheckNotApplicableInEnglish()
        ), Quality::notApplicable());
    }


    private function buildAttributeSpellcheck(array $resultByLocale): AttributeSpellcheck
    {
        $spellcheckResults = new SpellcheckResultByLocaleCollection();
        foreach ($resultByLocale as $locale => $result) {
            $spellcheckResults->add(new LocaleCode($locale), $result);
        }

        return new AttributeSpellcheck(new AttributeCode('color'), new \DateTimeImmutable(), $spellcheckResults);
    }

    private function buildAttributeOptionSpellcheck(array $resultByLocale): AttributeOptionSpellcheck
    {
        $spellcheckResults = new SpellcheckResultByLocaleCollection();
        foreach ($resultByLocale as $locale => $result) {
            $spellcheckResults->add(new LocaleCode($locale), $result);
        }

        return new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode('color'), 'red'),
            new \DateTimeImmutable(),
            $spellcheckResults
        );
    }

    public function givenAttributeSpellcheckNotApplicable(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck([]);
    }

    private function givenAttributeSpellcheckNotApplicableInEnglish(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck(['fr_FR' => SpellCheckResult::toImprove()]);
    }

    public function givenAttributeSpellcheckToImprove(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck(['en_US' => SpellCheckResult::toImprove()]);
    }

    public function givenAttributeSpellcheckGood(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck(['en_US' => SpellCheckResult::good()]);
    }

    private function givenAttributeSpellcheckGoodInEnglishAnToImproveInFrench(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck([
            'en_US' => SpellCheckResult::good(),
            'fr_FR' => SpellCheckResult::toImprove(),
        ]);
    }

    private function givenAttributeSpellcheckToImproveInEnglishAndGoodInFrench(): AttributeSpellcheck
    {
        return $this->buildAttributeSpellcheck([
            'en_US' => SpellCheckResult::toImprove(),
            'fr_FR' => SpellCheckResult::good(),
        ]);
    }

    public function givenAttributeOptionsSpellcheckToImprove(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck(['en_US' => SpellCheckResult::toImprove()]));
    }

    public function givenAttributeOptionsSpellcheckNotApplicable(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck([]));
    }

    private function givenAttributeOptionsSpellcheckNotApplicableInEnglish(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck(['fr_FR' => SpellCheckResult::good()]));
    }

    public function givenAttributeOptionsSpellcheckGood(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck(['en_US' => SpellCheckResult::good()]));
    }

    private function givenAttributeOptionsSpellcheckGoodInEnglishAnToImproveInFrench(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck(['en_US' => SpellCheckResult::good()]))
            ->add($this->buildAttributeOptionSpellcheck(['fr_FR' => SpellCheckResult::toImprove()]))
        ;
    }

    private function givenAttributeOptionsSpellcheckToImproveInEnglishAndGoodInFrench(): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->buildAttributeOptionSpellcheck(['en_US' => SpellCheckResult::toImprove()]))
            ->add($this->buildAttributeOptionSpellcheck(['fr_FR' => SpellCheckResult::good()]))
        ;
    }
}
