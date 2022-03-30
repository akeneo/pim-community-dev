<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;
use PhpSpec\ObjectBehavior;

class NumberSourceParameterSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(',');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NumberSourceParameter::class);
    }

    public function it_implements_source_parameter_interface()
    {
        $this->shouldBeAnInstanceOf(SourceParameterInterface::class);
    }

    public function it_returns_decimal_separator()
    {
        $this->getDecimalSeparator()->shouldReturn(',');
    }
}
