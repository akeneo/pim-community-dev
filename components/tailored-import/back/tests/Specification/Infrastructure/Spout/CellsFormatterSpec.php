<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use PhpSpec\ObjectBehavior;

class CellsFormatterSpec extends ObjectBehavior
{
    public function it_formats_empty_cells()
    {
        $this->formatCells([])->shouldReturn([]);
    }

    public function it_formats_string_cells()
    {
        $this->formatCells(['foo', 'bar', '000012'])->shouldReturn(['foo', 'bar', '000012']);
    }

    public function it_formats_number_cells()
    {
        $this->formatCells([12, 25.5, 800000, 0.00000003333, 1.0E-15])->shouldReturn(['12', '25.5', '800000', '0.00000003333', '0']);
    }

    public function it_formats_boolean_cells()
    {
        $this->formatCells([true, false])->shouldReturn(['TRUE', 'FALSE']);
    }

    public function it_does_not_support_datetime_cell()
    {
        $this->shouldThrow(\RuntimeException::class)->during('formatCells', [[
            \DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'),
            \DateTime::createFromFormat('Y-m-d H:i:s', '2020-06-02 15:00:00'),
        ]]);
    }

    public function it_throws_an_exception_when_type_is_unsupported()
    {
        $this->shouldThrow(\RuntimeException::class)->during('formatCells', [[new \stdClass()]]);
    }

    public function it_throws_an_exception_when_cell_is_invalid()
    {
        $this->shouldThrow(\RuntimeException::class)->during('formatCells', [[null]]);
    }
}
