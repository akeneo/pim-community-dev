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

namespace Specification\Akeneo\Test\Pim\TableAttribute\Domain\Config;

use Akeneo\Pim\TableAttribute\Domain\Config\Option;
use PhpSpec\ObjectBehavior;

class OptionSpec extends ObjectBehavior
{
    function it_can_be_instantiated()
    {
        $this->shouldBeAnInstanceOf(Option::class);
    }
}
