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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\Operation;

use PhpSpec\ObjectBehavior;

class MeasurementRoundingOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('up', 3);
    }

    public function it_returns_the_type()
    {
        $this->getType()->shouldReturn('up');
    }

    public function it_returns_the_precision()
    {
        $this->getPrecision()->shouldReturn(3);
    }
}
