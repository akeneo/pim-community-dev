<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith('color1', 'red', 'color_1', 'rouge');
        $this->shouldBeAnInstanceOf(AttributeOption::class);
    }
}
