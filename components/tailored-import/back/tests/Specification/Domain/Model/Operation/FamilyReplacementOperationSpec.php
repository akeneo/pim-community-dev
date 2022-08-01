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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use PhpSpec\ObjectBehavior;

class FamilyReplacementOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '00000000-0000-0000-0000-000000000000',
            [
                'video_game' => ['videogames', 'video-games'],
                33 => ['33export', 'beer'],
            ],
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FamilyReplacementOperation::class);
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
            'video_game' => ['videogames', 'video-games'],
            33 => ['33export', 'beer'],
        ]);
    }

    public function it_normalizes_operation(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'type' => 'family_replacement',
            'mapping' => [
                'video_game' => ['videogames', 'video-games'],
                33 => ['33export', 'beer'],
            ],
        ]);
    }

    public function it_returns_mapped_value(): void
    {
        $this->getMappedValue('videogames')->shouldReturn('video_game');
        $this->getMappedValue('video-games')->shouldReturn('video_game');
        $this->getMappedValue('33export')->shouldReturn('33');
        $this->getMappedValue('beer')->shouldReturn('33');
    }

    public function it_returns_null_when_not_mapped(): void
    {
        $this->beConstructedWith('00000000-0000-0000-0000-000000000000', []);
        $this->getMappedValue('unknown')->shouldReturn(null);
    }
}
