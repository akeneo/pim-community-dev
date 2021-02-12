<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary;

interface GetNumberOfWordsQueryInterface
{
    /**
     * @param string[] $locales
     *
     * @return int[] Number of words by locale ['locale_code' => number_of_words]
     */
    public function byLocales(array $locales): array;
}
