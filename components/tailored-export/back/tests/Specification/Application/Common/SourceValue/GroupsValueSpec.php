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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\GroupsValue;
use PhpSpec\ObjectBehavior;

class GroupsValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['group_code_1', 'group_code_2', 'group_code_3']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GroupsValue::class);
    }

    public function it_throws_an_exception_if_group_codes_are_invalid()
    {
        $this->beConstructedWith(['group_code_1', 2, 'group_code_3']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_group_codes()
    {
        $this->getGroupCodes()->shouldReturn(['group_code_1', 'group_code_2', 'group_code_3']);
    }
}
