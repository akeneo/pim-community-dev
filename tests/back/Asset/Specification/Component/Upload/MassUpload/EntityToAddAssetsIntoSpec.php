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

namespace Specification\Akeneo\Asset\Component\Upload\MassUpload;

use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Upload\MassUpload\EntityToAddAssetsInto;

class EntityToAddAssetsIntoSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foo', 'bar');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityToAddAssetsInto::class);
    }

    function it_returns_an_entity_identifier()
    {
        $this->getEntityIdentifier()->shouldReturn('foo');
    }

    function it_returns_an_attribute_code()
    {
        $this->getAttributeCode()->shouldReturn('bar');
    }
}
