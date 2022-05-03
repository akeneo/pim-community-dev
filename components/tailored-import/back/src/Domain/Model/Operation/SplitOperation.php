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

use Webmozart\Assert\Assert;

final class SplitOperation implements OperationInterface
{
    public const TYPE = 'split';

    /**
     * @param non-empty-string $separator
     */
    public function __construct(
        private string $separator,
    ) {
        Assert::stringNotEmpty($this->separator);
    }

    /**
     * @return non-empty-string
     */
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
