<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule;

use PhpSpec\ObjectBehavior;

/**
 * Denormalize product add rule actions.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ActionDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('MyClass', 'add');
    }

    function it_supports_add_type()
    {
        $this->supportsDenormalization(['type' => 'toto'], 'foo')->shouldReturn(false);
        $this->supportsDenormalization(['type' => 'add'], 'foo')->shouldReturn(true);
    }
}
