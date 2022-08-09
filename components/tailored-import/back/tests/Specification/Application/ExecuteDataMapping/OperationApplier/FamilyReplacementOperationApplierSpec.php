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

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\FamilyReplacementOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class FamilyReplacementOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_family_replacement_operation(): void
    {
        $this->supports(new FamilyReplacementOperation(
            $this->uuid,
            [
                'video_game' => ['videogames', 'video-game'],
                'board_game' => ['boardgames', 'board-games'],
            ],
        ))->shouldReturn(true);
    }

    public function it_applies_family_replacement_operation(): void
    {
        $operation = new FamilyReplacementOperation(
            $this->uuid,
            [
                'video_game' => ['videogames', 'video-game'],
                'board_game' => ['boardgames', 'board-games'],
                'card_game' => ['12'],
            ],
        );

        $this->applyOperation($operation, new StringValue('videogames'))->shouldBeLike(new StringValue('video_game'));
        $this->applyOperation($operation, new StringValue('12'))->shouldBeLike(new StringValue('card_game'));
    }

    public function it_returns_the_original_value_when_the_value_is_not_mapped(): void
    {
        $operation = new FamilyReplacementOperation(
            $this->uuid,
            [
                'video_game' => ['videogames', 'video-game'],
                'board_game' => ['boardgames', 'board-games'],
            ],
        );

        $this->applyOperation($operation, new StringValue('another_family'))->shouldBeLike(new StringValue('another_family'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new FamilyReplacementOperation(
            $this->uuid,
            [
                'video_game' => ['videogames', 'video-game'],
                'board_game' => ['boardgames', 'board-games'],
            ],
        );

        $this->shouldThrow(UnexpectedValueException::class)->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLTagsOperation($this->uuid);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException($operation, FamilyReplacementOperation::class, FamilyReplacementOperationApplier::class))
            ->during('applyOperation', [$operation, $value]);
    }
}
