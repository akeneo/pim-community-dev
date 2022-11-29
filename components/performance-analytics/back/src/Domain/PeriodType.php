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

namespace Akeneo\PerformanceAnalytics\Domain;

enum PeriodType: string
{
    // Not supported yet
    // case DAY = 'day';
    // case YEAR = 'year';

    case WEEK = 'week';
    case MONTH = 'month';
}
