<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleReferenceEntityReplacementOperation;
use PhpSpec\ObjectBehavior;

final class SimpleReferenceEntityReplacementOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [
                'adidas' => ['nike', 'reebok'],
                6 => ['foo', 'bar'],
                'int' => ['8'],
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SimpleReferenceEntityReplacementOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_uuid(): void
    {
        $this->getUuid()->shouldReturn('00000000-0000-0000-0000-000000000000');
    }

    public function it_returns_mapping(): void
    {
        $this->getMapping()->shouldReturn([
            'adidas' => ['nike', 'reebok'],
            6 => ['foo', 'bar'],
            'int' => ['8'],
        ]);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'simple_reference_entity_replacement',
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'mapping' => [
                'adidas' => ['nike', 'reebok'],
                6 => ['foo', 'bar'],
                'int' => ['8'],
            ],
        ]);
    }

    public function it_returns_mapped_value(): void
    {
        $this->getMappedValue('nike')->shouldReturn('adidas');
        $this->getMappedValue('reebok')->shouldReturn('adidas');
        $this->getMappedValue('foo')->shouldReturn('6');
        $this->getMappedValue('bar')->shouldReturn('6');
        $this->getMappedValue('8')->shouldReturn('int');
    }

    public function it_returns_null_when_not_mapped(): void
    {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', []);
        $this->getMappedValue('unknown')->shouldReturn(null);
    }
}
