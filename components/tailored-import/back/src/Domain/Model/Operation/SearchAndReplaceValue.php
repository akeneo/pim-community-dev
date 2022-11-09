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

final class SearchAndReplaceValue
{
    public function __construct(
        private string $uuid,
        private string $what,
        private string $with,
        private bool $caseSensitive,
    ) {
        Assert::uuid($uuid);
        Assert::stringNotEmpty($what);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getWhat(): string
    {
        return $this->what;
    }

    public function getWith(): string
    {
        return $this->with;
    }

    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'what' => $this->what,
            'with' => $this->with,
            'case_sensitive' => $this->caseSensitive,
        ];
    }
}
