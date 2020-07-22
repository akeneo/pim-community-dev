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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;

final class SpellcheckResultByLocaleCollection
{
    /** @var array */
    private $spellcheckResults = [];

    /** @var bool */
    private $toImprove = false;

    public function add(LocaleCode $localeCode, SpellCheckResult $spellCheckResult): self
    {
        $this->spellcheckResults[strval($localeCode)] = $spellCheckResult;

        if ($spellCheckResult->isToImprove()) {
            $this->toImprove = true;
        }

        return $this;
    }

    public function toArrayBool(): array
    {
        return array_map(function (SpellCheckResult $spellCheckResult) {
            return $spellCheckResult->isToImprove();
        }, $this->spellcheckResults);
    }

    public function isToImprove(): ?bool
    {
        return empty($this->spellcheckResults) ? null : $this->toImprove;
    }

    public function getLabelsToImproveNumber(): int
    {
        return count(array_filter($this->spellcheckResults, function (SpellCheckResult $result) {
            return $result->isToImprove();
        }));
    }

    public function getLocalesToImprove(): array
    {
        return array_keys(array_filter($this->spellcheckResults, function (SpellCheckResult $result) {
            return $result->isToImprove();
        }));
    }

    public function getLocaleResult(LocaleCode $localeCode): ?SpellCheckResult
    {
        if (! array_key_exists(strval($localeCode), $this->spellcheckResults)) {
            return null;
        }

        return $this->spellcheckResults[strval($localeCode)];
    }
}
