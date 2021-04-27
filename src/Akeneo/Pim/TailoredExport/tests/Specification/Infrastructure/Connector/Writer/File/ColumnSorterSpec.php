<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use PhpSpec\ObjectBehavior;

class ColumnSorterSpec extends ObjectBehavior
{
    function it_does_not_sort_column_for_the_moment()
    {
        $this->sort([
            'code',
            'family',
            'description-de_DE',
            'name',
        ])->shouldReturn([
            'code',
            'family',
            'description-de_DE',
            'name'
        ]);
    }
}
