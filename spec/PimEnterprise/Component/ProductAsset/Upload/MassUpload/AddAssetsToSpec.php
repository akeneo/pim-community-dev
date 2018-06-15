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

namespace spec\PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetsTo;

class AddAssetsToSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(42, 'foobar');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddAssetsTo::class);
    }

    function it_returns_an_entity_id()
    {
        $this->getEntityId()->shouldReturn(42);
    }

    function it_returns_an_attribute_code()
    {
        $this->getAttributeCode()->shouldReturn('foobar');
    }
}
