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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class CleanHTMLOperationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CleanHTMLOperation::class);
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
        $this->getModes()->shouldReturn([CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]);
    }

    public function it_normalize_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'modes' => ['remove'],
            'type' => 'clean_html',
        ]);
    }
}
