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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\ArrayConverter\FlatToStandard\User;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

class UserSpec extends ObjectBehavior
{
    function let(ArrayConverterInterface $baseConverter)
    {
        $this->beConstructedWith($baseConverter);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_is_a_user_array_converter()
    {
        $this->shouldHaveType(User::class);
    }

    function it_converts_proposal_related_properties(ArrayConverterInterface $baseConverter)
    {
        $item = [
            'foo' => 'bar',
            'bar' => 'baz',
            'proposal_state_notification' => '1',
            'proposals_to_review_notification' => '0',
        ];
        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
            'proposal_state_notification' => true,
            'proposals_to_review_notification' => false,
        ];

        $baseConverter->convert($item, [])->shouldBeCalled()->willReturn($expected);

        $this->convert($item)->shouldReturn($expected);
    }
}
