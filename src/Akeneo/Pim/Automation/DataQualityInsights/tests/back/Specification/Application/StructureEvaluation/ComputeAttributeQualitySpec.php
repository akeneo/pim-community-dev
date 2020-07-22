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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheckCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;

final class ComputeAttributeQualitySpec extends ObjectBehavior
{
    public function let(
        GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery
    ) {
        $this->beConstructedWith($getAttributeSpellcheckQuery, $getAttributeOptionSpellcheckQuery);
    }

    public function it_is_processing_if_there_is_still_no_spellcheck_whatever_the_options_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(null);
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->shouldNotBeCalled();

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::processing());
    }

    public function it_is_to_improve_if_the_attribute_spellcheck_is_to_improve_whatever_the_options_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        ));
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->shouldNotBeCalled();

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::toImprove());
    }

    public function it_is_to_improve_if_the_attribute_spellcheck_is_not_to_improve_but_there_is_a_option_spellcheck_to_improve(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckGood($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAttributeOptionsSpellchecksWithOneToImprove($attributeCode)
        );
        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::toImprove());

        $attributeCode = new AttributeCode('material');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckNotApplicable($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAttributeOptionsSpellchecksWithOneToImprove($attributeCode)
        );
        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::toImprove());
    }

    public function it_is_good_if_the_attribute_spellcheck_is_good_and_there_are_no_options_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckGood($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            new AttributeOptionSpellcheckCollection()
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::good());
    }

    public function it_is_good_if_the_attribute_spellcheck_is_good_and_there_are_only_good_option_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckGood($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenOnlyGoodAttributeOptionsSpellchecks($attributeCode)
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::good());
    }

    public function it_is_good_if_the_attribute_spellcheck_is_good_and_there_are_only_not_applicable_option_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckGood($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenOnlyNotApplicableAttributeOptionsSpellchecks($attributeCode)
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::good());
    }

    public function it_is_good_if_the_attribute_spellcheck_is_not_applicable_but_there_are_only_good_option_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckNotApplicable($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenOnlyGoodAttributeOptionsSpellchecks($attributeCode)
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::good());
    }

    public function it_is_not_applicable_if_the_attribute_spellcheck_is_not_applicable_an_there_are_no_options_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckNotApplicable($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            new AttributeOptionSpellcheckCollection()
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::notApplicable());
    }

    public function it_is_not_applicable_if_the_attribute_spellcheck_is_not_applicable_an_there_are_only_not_applicable_options_spellchecks(
        $getAttributeSpellcheckQuery,
        $getAttributeOptionSpellcheckQuery
    ) {
        $attributeCode = new AttributeCode('color');
        $getAttributeSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenAnAttributeSpellcheckNotApplicable($attributeCode)
        );
        $getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode)->willReturn(
            $this->givenOnlyNotApplicableAttributeOptionsSpellchecks($attributeCode)
        );

        $this->byAttributeCode($attributeCode)->shouldBeLike(Quality::notApplicable());
    }

    private function givenAnAttributeSpellcheckGood(AttributeCode $attributeCode): AttributeSpellcheck
    {
        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        );
    }

    private function givenAnAttributeSpellcheckNotApplicable(AttributeCode $attributeCode): AttributeSpellcheck
    {
        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );
    }

    private function givenAttributeOptionsSpellchecksWithOneToImprove(AttributeCode $attributeCode): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->givenAnAttributeOptionSpellcheckGood($attributeCode, 'A'))
            ->add($this->givenAnAttributeOptionSpellcheckNotApplicable($attributeCode, 'B'))
            ->add($this->givenAnAttributeOptionSpellcheckToImprove($attributeCode, 'C'));
    }

    private function givenOnlyGoodAttributeOptionsSpellchecks(AttributeCode $attributeCode): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->givenAnAttributeOptionSpellcheckGood($attributeCode, 'A'))
            ->add($this->givenAnAttributeOptionSpellcheckGood($attributeCode, 'B'));
    }

    private function givenOnlyNotApplicableAttributeOptionsSpellchecks(AttributeCode $attributeCode): AttributeOptionSpellcheckCollection
    {
        return (new AttributeOptionSpellcheckCollection())
            ->add($this->givenAnAttributeOptionSpellcheckNotApplicable($attributeCode, 'A'))
            ->add($this->givenAnAttributeOptionSpellcheckNotApplicable($attributeCode, 'B'));
    }

    private function givenAnAttributeOptionSpellcheckToImprove(AttributeCode $attributeCode, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, $option),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );
    }

    private function givenAnAttributeOptionSpellcheckGood(AttributeCode $attributeCode, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, $option),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        );
    }

    private function givenAnAttributeOptionSpellcheckNotApplicable(AttributeCode $attributeCode, string $option): AttributeOptionSpellcheck
    {
        return new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, $option),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );
    }
}
