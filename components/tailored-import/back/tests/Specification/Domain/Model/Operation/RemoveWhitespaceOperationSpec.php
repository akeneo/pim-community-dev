<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use PhpSpec\ObjectBehavior;

final class RemoveWhitespaceOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [
                'trim',
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RemoveWhitespaceOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_uuid(): void
    {
        $this->getUuid()->shouldReturn('00000000-0000-0000-0000-000000000000');
    }

    public function it_returns_modes(): void
    {
        $this->getModes()->shouldReturn(['trim']);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'modes' => [
                'trim',
            ],
            'type' => 'remove_whitespace',
        ]);
    }

    public function it_throws_exception_when_it_is_constructed_with_a_wrong_mode(): void {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', ['invalid']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
