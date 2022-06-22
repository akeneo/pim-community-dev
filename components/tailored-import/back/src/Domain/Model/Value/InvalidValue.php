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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

final class InvalidValue implements ValueInterface
{
    public function __construct(
        private string $errorMessage,
    ) {
    }

    public function getValue(): mixed
    {
        throw new \RuntimeException('You can\'t access to value on an InvalidValue object');
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
