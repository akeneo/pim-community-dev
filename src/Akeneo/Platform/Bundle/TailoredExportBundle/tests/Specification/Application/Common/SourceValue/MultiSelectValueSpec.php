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

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MultiSelectValue;
use PhpSpec\ObjectBehavior;

class MultiSelectValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['option_code_1', 'option_code_2', 'option_code_3']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(MultiSelectValue::class);
    }

    public function it_throws_an_exception_if_option_codes_are_invalid()
    {
        $this->beConstructedWith(['option_code_1', 2, 'option_code_3']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_option_codes()
    {
        $this->getOptionCodes()->shouldReturn(['option_code_1', 'option_code_2', 'option_code_3']);
    }
}
