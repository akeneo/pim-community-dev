<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

interface MultipleTextsChecker
{
    public function check(array $texts, LocaleCode $localeCode): array;
}
