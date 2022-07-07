<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

interface GetDictionaryLastUpdateDateByLocaleQueryInterface
{
    public function execute(LocaleCode $localeCode): ?\DateTimeImmutable;
}
