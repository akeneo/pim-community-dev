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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class EvaluateAttributeOptionLabelsSpellingSpec extends ObjectBehavior
{
    public function let(
        GetAttributeOptionLabelsQueryInterface $getAttributeOptionLabelsQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextChecker $textChecker,
        Clock $clock,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($getAttributeOptionLabelsQuery, $supportedLocaleValidator, $textChecker, $clock, $logger);
    }

    public function it_evaluates_the_spelling_of_the_labels_of_a_given_attribute_option(
        $getAttributeOptionLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock,
        TextCheckResultCollection $englishTextCheckResult,
        TextCheckResultCollection $frenchTextCheckResult
    ) {
        $attributeOptionCode = new AttributeOptionCode(new AttributeCode('color'), 'red');
        $getAttributeOptionLabelsQuery->byCode($attributeOptionCode)->willReturn([
            'en_US' => 'Red',
            'fr_FR' => 'Roge',
            'de_DE' => null,
        ]);

        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $textChecker->check('Red', $localeEn)->willReturn($englishTextCheckResult);
        $textChecker->check('Roge', $localeFr)->willReturn($frenchTextCheckResult);

        $englishTextCheckResult->count()->willReturn(0);
        $frenchTextCheckResult->count()->willReturn(1);

        $now = new \DateTimeImmutable('2020-03-12 08:32:51');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeOptionSpellcheck = new AttributeOptionSpellcheck(
            $attributeOptionCode,
            $now,
            (new SpellcheckResultByLocaleCollection())
                ->add($localeEn, SpellCheckResult::good())
                ->add($localeFr, SpellCheckResult::toImprove())
        );

        $this->evaluate($attributeOptionCode)->shouldBeLike($expectedAttributeOptionSpellcheck);
    }

    public function it_does_not_evaluate_labels_of_not_supported_locales(
        $getAttributeOptionLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock
    ) {
        $attributeOptionCode = new AttributeOptionCode(new AttributeCode('color'), 'red');
        $getAttributeOptionLabelsQuery->byCode($attributeOptionCode)->willReturn([
            'sl_SL' => 'Cervená'
        ]);

        $supportedLocaleValidator->isSupported(new LocaleCode('sl_SL'))->willReturn(false);
        $textChecker->check(Argument::cetera())->shouldNotBeCalled();

        $now = new \DateTimeImmutable('2020-03-12 08:32:52');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeOptionSpellcheck = new AttributeOptionSpellcheck($attributeOptionCode, $now, new SpellcheckResultByLocaleCollection());
        $this->evaluate($attributeOptionCode)->shouldBeLike($expectedAttributeOptionSpellcheck);
    }

    public function it_continues_to_evaluate_when_an_error_occurs_on_one_label(
        $getAttributeOptionLabelsQuery,
        $supportedLocaleValidator,
        $textChecker,
        $clock,
        TextCheckResultCollection $frenchTextCheckResult
    ) {
        $attributeOptionCode = new AttributeOptionCode(new AttributeCode('color'), 'red');
        $getAttributeOptionLabelsQuery->byCode($attributeOptionCode)->willReturn([
            'en_US' => 'Red',
            'fr_FR' => 'Roge',
        ]);

        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $textChecker->check('Red', $localeEn)->willThrow(\Exception::class);
        $textChecker->check('Roge', $localeFr)->willReturn($frenchTextCheckResult);

        $frenchTextCheckResult->count()->willReturn(1);

        $now = new \DateTimeImmutable('2020-03-12 08:32:51');
        $clock->getCurrentTime()->willReturn($now);

        $expectedAttributeOptionSpellcheck = new AttributeOptionSpellcheck(
            $attributeOptionCode,
            $now,
            (new SpellcheckResultByLocaleCollection())
                ->add($localeFr, SpellCheckResult::toImprove())
        );

        $this->evaluate($attributeOptionCode)->shouldBeLike($expectedAttributeOptionSpellcheck);
    }
}
