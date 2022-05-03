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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class CleanHTMLTagsOperationSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(CleanHTMLTagsOperation::class);
    }

    public function it_implements_operation_interface()
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_normalize_operation()
    {
        $this->normalize()->shouldReturn([
            'type' => 'clean_html_tags',
        ]);
    }
}
