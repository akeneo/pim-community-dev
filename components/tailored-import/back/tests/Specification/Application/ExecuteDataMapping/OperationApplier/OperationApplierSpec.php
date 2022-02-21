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

use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use PhpSpec\ObjectBehavior;

class OperationApplierSpec extends ObjectBehavior
{
    public function it_does_nothing_for_the_moment()
    {
        $row = new Row([
            '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref1',
            '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 1',
        ]);

        $this->applyOperations([], $row, '25621f5a-504f-4893-8f0c-9f1b0076e53e')->shouldReturn('ref1');
    }
}
