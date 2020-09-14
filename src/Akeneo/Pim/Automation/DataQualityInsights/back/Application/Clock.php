<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

interface Clock
{
    const TIME_FORMAT = 'Y-m-d H:i:s';

    public function getCurrentTime(): \DateTimeImmutable;

    public function fromString(string $date): \DateTimeImmutable;

    public function fromTimestamp(int $timestamp): \DateTimeImmutable;
}
