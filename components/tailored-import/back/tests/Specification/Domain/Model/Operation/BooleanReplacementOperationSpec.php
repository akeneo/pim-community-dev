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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class BooleanReplacementOperationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [
                'true' => ['oui'],
                'false' => ['non'],
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(BooleanReplacementOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_can_tell_if_a_value_is_mapped(): void
    {
        $this->hasMappedValue('oui')->shouldReturn(true);
        $this->hasMappedValue('non')->shouldReturn(true);

        $this->hasMappedValue('ja')->shouldReturn(false);
        $this->hasMappedValue('nein')->shouldReturn(false);
    }

    public function it_can_get_the_mapped_value(): void
    {
        $this->getMappedValue('oui')->shouldReturn(true);
        $this->getMappedValue('non')->shouldReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('getMappedValue', ['not found']);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'type' => 'boolean_replacement',
            'mapping' => [
                'true' => ['oui'],
                'false' => ['non'],
            ],
        ]);
    }
}
