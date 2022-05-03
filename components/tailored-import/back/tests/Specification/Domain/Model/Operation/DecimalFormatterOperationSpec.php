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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\DecimalFormatterOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class DecimalFormatterOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(',');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DecimalFormatterOperation::class);
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
            'type' => 'decimal_formatter',
            'decimal_separator' => ',',
        ]);
    }
}
