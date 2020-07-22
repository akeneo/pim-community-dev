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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Psr\Log\LoggerInterface;

class EvaluateAttributeOptionLabelsSpelling
{
    /** @var GetAttributeOptionLabelsQueryInterface */
    private $getAttributeOptionLabelsQuery;

    /** @var SupportedLocaleValidator */
    private $supportedLocaleValidator;

    /** @var TextChecker */
    private $textChecker;

    /** @var Clock */
    private $clock;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        GetAttributeOptionLabelsQueryInterface $getAttributeOptionLabelsQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextChecker $textChecker,
        Clock $clock,
        LoggerInterface $logger
    ) {
        $this->getAttributeOptionLabelsQuery = $getAttributeOptionLabelsQuery;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
        $this->textChecker = $textChecker;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function evaluate(AttributeOptionCode $attributeOptionCode): AttributeOptionSpellcheck
    {
        $optionLabels = $this->getAttributeOptionLabelsQuery->byCode($attributeOptionCode);
        $spellCheckResults = new SpellcheckResultByLocaleCollection();

        foreach ($optionLabels as $locale => $optionLabel) {
            $localeCode = new LocaleCode($locale);
            $spellCheckResult = $this->evaluateOptionLabel($localeCode, $optionLabel);
            if (null !== $spellCheckResult) {
                $spellCheckResults->add($localeCode, $spellCheckResult);
            }
        }

        return new AttributeOptionSpellcheck($attributeOptionCode, $this->clock->getCurrentTime(), $spellCheckResults);
    }

    private function evaluateOptionLabel(LocaleCode $localeCode, ?string $optionLabel): ?SpellCheckResult
    {
        if (null === $optionLabel || !$this->supportedLocaleValidator->isSupported($localeCode)) {
            return null;
        }

        try {
            $textCheckResult = $this->textChecker->check($optionLabel, $localeCode);
        } catch (\Throwable $exception) {
            $this->logger->error('An error occurred during an attribute option spelling evaluation', ['error_code' => 'error_during_spelling_evaluation', 'error_message' => $exception->getMessage()]);
            return null;
        }

        return $textCheckResult->count() > 0 ? SpellCheckResult::toImprove() : SpellCheckResult::good();
    }
}
