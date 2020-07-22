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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Psr\Log\LoggerInterface;

class EvaluateAttributeLabelsSpelling
{
    /** @var GetAttributeLabelsQueryInterface */
    private $getAttributeLabelsQuery;

    /** @var SupportedLocaleValidator */
    private $supportedLocaleValidator;

    /** @var TextChecker */
    private $textChecker;

    /** @var Clock */
    private $clock;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        GetAttributeLabelsQueryInterface $getAttributeLabelsQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextChecker $textChecker,
        Clock $clock,
        LoggerInterface $logger
    ) {
        $this->getAttributeLabelsQuery = $getAttributeLabelsQuery;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
        $this->textChecker = $textChecker;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function evaluate(AttributeCode $attributeCode): AttributeSpellcheck
    {
        $labels = $this->getAttributeLabelsQuery->byCode($attributeCode);
        $spellCheckResults = new SpellcheckResultByLocaleCollection();

        foreach ($labels as $locale => $label) {
            $localeCode = new LocaleCode($locale);
            $spellCheckResult = $this->evaluateLabel($localeCode, $label);
            if (null !== $spellCheckResult) {
                $spellCheckResults->add($localeCode, $spellCheckResult);
            }
        }

        return new AttributeSpellcheck($attributeCode, $this->clock->getCurrentTime(), $spellCheckResults);
    }

    private function evaluateLabel(LocaleCode $localeCode, ?string $label): ?SpellCheckResult
    {
        if (null === $label || !$this->supportedLocaleValidator->isSupported($localeCode)) {
            return null;
        }

        try {
            $textCheckResult = $this->textChecker->check($label, $localeCode);
        } catch (\Throwable $exception) {
            $this->logger->error('An error occurred during an attribute labels spelling evaluation', ['error_code' => 'error_during_spelling_evaluation', 'error_message' => $exception->getMessage()]);
            return null;
        }

        return $textCheckResult->count() > 0 ? SpellCheckResult::toImprove() : SpellCheckResult::good();
    }
}
