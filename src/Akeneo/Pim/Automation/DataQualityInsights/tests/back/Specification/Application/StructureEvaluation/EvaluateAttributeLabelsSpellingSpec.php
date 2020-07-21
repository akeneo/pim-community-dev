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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class EvaluateAttributeLabelsSpellingSpec extends ObjectBehavior
{
    public function let(
        GetAttributeLabelsQueryInterface $getAttributeLabelsQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextChecker $textChecker,
        Clock $clock,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($getAttributeLabelsQuery, $supportedLocaleValidator, $textChecker, $clock, $logger);
    }

    public function it_evaluates_the_spelling_of_the_labels_of_a_given_attribute(
        $getAttributeLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock,
        TextCheckResultCollection $englishTextCheckResult,
        TextCheckResultCollection $frenchTextCheckResult
    ) {
        $attributeCode = new AttributeCode('description');
        $getAttributeLabelsQuery->byCode($attributeCode)->willReturn([
            'en_US' => 'Description',
            'fr_FR' => 'Descriiption',
            'de_DE' => null,
        ]);

        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $textChecker->check('Description', $localeEn)->willReturn($englishTextCheckResult);
        $textChecker->check('Descriiption', $localeFr)->willReturn($frenchTextCheckResult);

        $englishTextCheckResult->count()->willReturn(0);
        $frenchTextCheckResult->count()->willReturn(1);

        $now = new \DateTimeImmutable('2020-03-12 08:32:51');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeSpellcheck = new AttributeSpellcheck(
            $attributeCode,
            $now,
            (new SpellcheckResultByLocaleCollection())
                ->add($localeEn, SpellCheckResult::good())
                ->add($localeFr, SpellCheckResult::toImprove())
        );

        $this->evaluate($attributeCode)->shouldBeLike($expectedAttributeSpellcheck);
    }

    public function it_does_not_evaluate_labels_of_not_supported_locales(
        $getAttributeLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock
    ) {
        $attributeCode = new AttributeCode('description');
        $getAttributeLabelsQuery->byCode($attributeCode)->willReturn([
            'sl_SL' => 'Popis'
        ]);

        $supportedLocaleValidator->isSupported(new LocaleCode('sl_SL'))->willReturn(false);
        $textChecker->check(Argument::cetera())->shouldNotBeCalled();

        $now = new \DateTimeImmutable('2020-03-12 08:32:52');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeSpellcheck = new AttributeSpellcheck($attributeCode, $now, new SpellcheckResultByLocaleCollection());

        $this->evaluate($attributeCode)->shouldBeLike($expectedAttributeSpellcheck);
    }

    public function it_continues_to_evaluate_when_an_error_occurs_on_one_label(
        $getAttributeLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock,
        TextCheckResultCollection $frenchTextCheckResult
    ) {
        $attributeCode = new AttributeCode('description');
        $getAttributeLabelsQuery->byCode($attributeCode)->willReturn([
            'en_US' => 'Description',
            'fr_FR' => 'Descriiption',
        ]);

        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $textChecker->check('Description', $localeEn)->willThrow(\Exception::class);
        $textChecker->check('Descriiption', $localeFr)->willReturn($frenchTextCheckResult);

        $frenchTextCheckResult->count()->willReturn(1);

        $now = new \DateTimeImmutable('2020-03-12 08:32:51');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeSpellcheck = new AttributeSpellcheck(
            $attributeCode,
            $now,
            (new SpellcheckResultByLocaleCollection())
                ->add($localeFr, SpellCheckResult::toImprove())
        );

        $this->evaluate($attributeCode)->shouldBeLike($expectedAttributeSpellcheck);
    }
}
