<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeCode
{
    public const RESERVED_CODES = ['code', 'label', 'image'];
    public const REGEX = '/^[a-zA-Z0-9_]+$/';
    public const MAX_LENGTH = 255;

    private string $code;

    private function __construct(string $code)
    {
        Assert::stringNotEmpty($code, 'Attribute code cannot be empty');
        Assert::maxLength(
            $code,
            self::MAX_LENGTH,
            sprintf('Attribute code cannot be longer than 255 characters, %d string long given', strlen($code))
        );
        Assert::regex(
            $code,
            self::REGEX,
            sprintf('Attribute code may contain only letters, numbers and underscores. "%s" given', $code)
        );

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function equals(AttributeCode $code): bool
    {
        return $this->code === $code->code;
    }
}
