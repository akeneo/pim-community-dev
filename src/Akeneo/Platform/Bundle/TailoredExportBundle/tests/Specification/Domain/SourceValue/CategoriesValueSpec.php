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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValue\CategoriesValue;
use PhpSpec\ObjectBehavior;

class CategoriesValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['category_code_1', 'category_code_2', 'category_code_3']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(CategoriesValue::class);
    }

    public function it_throws_an_exception_if_category_codes_are_invalid()
    {
        $this->beConstructedWith(['category_code_1', 2, 'category_code_3']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_category_codes()
    {
        $this->getCategoryCodes()->shouldReturn(['category_code_1', 'category_code_2', 'category_code_3']);
    }
}
