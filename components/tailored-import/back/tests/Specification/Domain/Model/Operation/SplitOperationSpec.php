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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use PhpSpec\ObjectBehavior;

class SplitOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', ',');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SplitOperation::class);
    }

    public function it_implements_operation_interface()
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_uuid(): void
    {
        $this->getUuid()->shouldReturn('00000000-0000-0000-0000-000000000000');
    }

    public function it_returns_separator()
    {
        $this->getSeparator()->shouldReturn(',');
    }

    public function it_normalize_operation()
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'type' => 'split',
            'separator' => ',',
        ]);
    }
}
