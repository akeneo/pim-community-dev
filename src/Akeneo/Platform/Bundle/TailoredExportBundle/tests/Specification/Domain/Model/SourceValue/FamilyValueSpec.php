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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyValue;
use PhpSpec\ObjectBehavior;

class FamilyValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('a_family_code');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FamilyValue::class);
    }

    public function it_returns_the_family_code()
    {
        $this->getFamilyCode()->shouldReturn('a_family_code');
    }
}
