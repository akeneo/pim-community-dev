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

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SplitOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_split_operation(): void
    {
        $this->supports(new SplitOperation(','))->shouldReturn(true);
    }

    public function it_applies_split_operation(): void
    {
        $operation = new SplitOperation(',');
        $value = new StringValue('value1,value2, value3');

        $this->applyOperation($operation, $value)
            ->shouldBeLike(new ArrayValue(['value1', 'value2', ' value3']));
    }
}
