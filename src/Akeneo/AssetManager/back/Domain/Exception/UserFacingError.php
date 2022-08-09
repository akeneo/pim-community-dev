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

namespace Akeneo\AssetManager\Domain\Exception;

abstract class UserFacingError extends \Exception
{
    /**
     * @param array<string,mixed> $translationParameters
     */
    public function __construct(
        private string $translationKey,
        private array $translationParameters
    ) {
        parent::__construct();
    }

    public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @return array<string,mixed>
     */
    public function translationParameters(): array
    {
        return $this->translationParameters;
    }
}
