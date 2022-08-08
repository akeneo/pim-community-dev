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

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToNumberOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FormatFloatOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

final class FormatFloatOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', ',');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FormatFloatOperation::class);
    }

    public function it_implements_operation_interface()
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_decimal_separator()
    {
        $this->getDecimalSeparator()->shouldReturn(',');
    }

    public function it_normalize_operation()
    {
        $this->normalize()->shouldReturn([
            'type' => 'format_float',
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'decimal_separator' => ',',
        ]);
    }
}
