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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class MultiSelectReplacementOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            [
                'adidas' => ['nike', 'reebok'],
                6 => ['foo', 'bar'],
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MultiSelectReplacementOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_mapping(): void
    {
        $this->getMapping()->shouldReturn([
            'adidas' => ['nike', 'reebok'],
            6 => ['foo', 'bar'],
        ]);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'multi_select_replacement',
            'mapping' => [
                'adidas' => ['nike', 'reebok'],
                6 => ['foo', 'bar'],
            ],
        ]);
    }

    public function it_returns_mapped_value(): void
    {
        $this->getMappedValue('nike')->shouldReturn('adidas');
        $this->getMappedValue('reebok')->shouldReturn('adidas');
        $this->getMappedValue('foo')->shouldReturn('6');
        $this->getMappedValue('bar')->shouldReturn('6');
    }

    public function it_returns_null_when_not_mapped(): void
    {
        $this->beConstructedWith([]);
        $this->getMappedValue('unknown')->shouldReturn(null);
    }
}
