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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\Selection\Date;

use PhpSpec\ObjectBehavior;

class DateSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('yyyy-mm-dd');
    }

    public function it_returns_the_format()
    {
        $this->getFormat()->shouldReturn('yyyy-mm-dd');
    }

    public function it_checit_checks_that_the_format_is_valid()
    {
        $this->beConstructedWith('yyy-mm-dd');

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
