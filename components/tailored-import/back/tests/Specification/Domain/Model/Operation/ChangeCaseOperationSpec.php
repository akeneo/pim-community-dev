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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

final class ChangeCaseOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            ChangeCaseOperation::MODE_UPPERCASE
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ChangeCaseOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_uuid(): void
    {
        $this->getUuid()->shouldReturn('00000000-0000-0000-0000-000000000000');
    }

    public function it_returns_mode(): void
    {
        $this->getMode()->shouldReturn(ChangeCaseOperation::MODE_UPPERCASE);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'mode' => ChangeCaseOperation::MODE_UPPERCASE,
            'type' => 'change_case',
        ]);
    }

    public function it_throws_exception_when_it_is_constructed_with_a_wrong_mode(): void {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', 'invalid');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
