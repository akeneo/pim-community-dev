<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Clock
{
    const TIME_FORMAT = 'Y-m-d H:i:s';

    public function getCurrentTime(): \DateTimeImmutable;

    public function fromString(string $date): \DateTimeImmutable;

    public function fromTimestamp(int $timestamp): \DateTimeImmutable;
}
