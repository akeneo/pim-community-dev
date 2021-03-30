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
class Prefix
{
    public const EMPTY = null;

    /** @var ?string */
    private $prefix;

    private function __construct(?string $prefix)
    {
        $this->prefix = $prefix;
    }

    public static function fromString(?string $prefix): self
    {
        return new self('' === $prefix ? self::EMPTY : $prefix);
    }

    public static function createEmpty(): self
    {
        return new self(self::EMPTY);
    }

    public function isEmpty(): bool
    {
        return self::EMPTY === $this->prefix;
    }

    public function normalize(): ?string
    {
        return $this->prefix;
    }

    public function stringValue(): string
    {
        return $this->prefix === self::EMPTY ? '' : $this->prefix;
    }
}
