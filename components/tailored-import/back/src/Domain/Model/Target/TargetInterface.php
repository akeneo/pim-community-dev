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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Target;

interface TargetInterface
{
    public const ACTION_ADD = 'add';
    public const ACTION_SET = 'set';
    public const IF_EMPTY_CLEAR = 'clear';
    public const IF_EMPTY_SKIP = 'skip';

    public function getCode(): string;

    public function getType(): string;

    public function getActionIfNotEmpty(): string;

    public function getActionIfEmpty(): string;

    public function normalize(): array;
}
