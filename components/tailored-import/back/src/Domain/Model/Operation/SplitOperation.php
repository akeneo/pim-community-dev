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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

final class SplitOperation implements OperationInterface
{
    public const TYPE = 'split';

    public function __construct(
        private string $separator,
    ) {
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'separator' => $this->separator,
        ];
    }
}
