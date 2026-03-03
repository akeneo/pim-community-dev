<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Domain\Model;

use PhpSpec\ObjectBehavior;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StatusSpec extends ObjectBehavior
{
    public function it_is_constructable_with_status()
    {
        $this->beConstructedThrough('fromStatus', [3]);
        $this->getStatus()->shouldReturn(3);
        $this->getLabel()->shouldReturn('IN_PROGRESS');
    }

    public function it_is_constructable_with_label()
    {
        $this->beConstructedThrough('fromLabel', ['IN_PROGRESS']);
        $this->getStatus()->shouldReturn(3);
        $this->getLabel()->shouldReturn('IN_PROGRESS');
    }

    public function it_throws_exception_when_trying_to_construct_it_with_invalid_status()
    {
        $this->beConstructedThrough('fromStatus', [0]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedThrough('fromStatus', [26]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_exception_when_trying_to_construct_it_with_invalid_label()
    {
        $this->beConstructedThrough('fromLabel', ['invalid']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
