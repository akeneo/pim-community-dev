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

class RowCleanerSpec extends ObjectBehavior
{
    public function it_only_remove_empty_columns_at_end()
    {
        $this->removeTrailingEmptyColumns([])->shouldReturn([]);
        $this->removeTrailingEmptyColumns(['', ''])->shouldReturn([]);
        $this->removeTrailingEmptyColumns(['c', '', ''])->shouldReturn(['c']);
        $this->removeTrailingEmptyColumns(['c', '', 'd', ''])->shouldReturn(['c', '', 'd']);
        $this->removeTrailingEmptyColumns(['c', '0', 'd', ''])->shouldReturn(['c', '0', 'd']);
        $this->removeTrailingEmptyColumns(['', 'a', '', 'b', 'c', '', ''])->shouldReturn(['', 'a', '', 'b', 'c']);
    }
}
