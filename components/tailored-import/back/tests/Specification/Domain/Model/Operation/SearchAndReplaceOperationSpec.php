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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceValue;
use PhpSpec\ObjectBehavior;

class SearchAndReplaceOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [
                new SearchAndReplaceValue(
                    '00000000-0000-0000-0000-000000000001',
                    'replace me',
                    'with this text',
                    false,
                ),
                new SearchAndReplaceValue(
                    '00000000-0000-0000-0000-000000000002',
                    'another replace me',
                    'with this other text',
                    true,
                ),
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SearchAndReplaceOperation::class);
    }

    public function it_implements_operation_interface(): void
    {
        $this->shouldBeAnInstanceOf(OperationInterface::class);
    }

    public function it_returns_uuid(): void
    {
        $this->getUuid()->shouldReturn('00000000-0000-0000-0000-000000000000');
    }

    public function it_returns_replacements(): void
    {
        $this->getReplacements()->shouldBeLike([
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000001',
                'replace me',
                'with this text',
                false,
            ),
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000002',
                'another replace me',
                'with this other text',
                true,
            ),
        ]);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'type' => 'search_and_replace',
            'replacements' => [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'what' => 'replace me',
                    'with' => 'with this text',
                    'case_sensitive' => false,
                ],
                [
                    'uuid' => '00000000-0000-0000-0000-000000000002',
                    'what' => 'another replace me',
                    'with' => 'with this other text',
                    'case_sensitive' => true,
                ],
            ],
        ]);
    }
}
