<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Suffix
{
    public const EMPTY = null;

    /** @var ?string */
    private $suffix;

    private function __construct(?string $suffix)
    {
        $this->suffix = $suffix;
    }

    public static function fromString(?string $suffix): self
    {
        return new self('' === $suffix ? self::EMPTY : $suffix);
    }

    public static function empty(): self
    {
        return new self(self::EMPTY);
    }

    public function isEmpty(): bool
    {
        return self::EMPTY === $this->suffix;
    }

    public function normalize(): ?string
    {
        return $this->suffix;
    }

    public function stringValue(): string
    {
        return $this->suffix === self::EMPTY ? '' : $this->suffix;
    }
}
