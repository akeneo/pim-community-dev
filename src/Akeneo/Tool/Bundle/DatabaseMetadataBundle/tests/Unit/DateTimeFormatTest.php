<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\Unit;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;

use DateTime;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DateTimeFormatTest extends TestCase
{
    public function test_it_datetime_formatter_from_string(): void
    {
        $dateInput = '2022-03-07 13:56:37';
        $dateFormat = DateTimeFormat::formatFromString();
        $dateFormatted = $dateFormat($dateInput);

        $dateExpected = new DateTime($dateInput);

        Assert::assertEquals($dateExpected, $dateFormatted);
    }

    public function test_it_datetime_formatter_from_integer(): void
    {
        $dateInput = 1646661022;
        $dateFormat = DateTimeFormat::formatFromInt();
        $dateFormatted = $dateFormat($dateInput);

        $dateExpected = (new \DateTime)->setTimestamp($dateInput);

        Assert::assertEquals($dateExpected, $dateFormatted);
    }

    public function test_it_datetime_formatter_from_iso(): void
    {
        $dateInput = '2022-03-07T13:56:37.000000+0000';

        $dateFormat = DateTimeFormat::formatFromIso();
        $dateFormatted = $dateFormat($dateInput);

        $dateExpected= new DateTime($dateInput);

        Assert::assertEquals($dateExpected, $dateFormatted);
    }
}
