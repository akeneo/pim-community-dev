<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCode implements \Stringable
{
    public const REGULAR_EXPRESSION = '/^[a-zA-Z0-9_]+$/';

    private function __construct(private string $code)
    {
        Assert::stringNotEmpty($code, 'Option code cannot be empty');
        Assert::maxLength(
            $code,
            255,
            sprintf('Option code cannot be longer than 255 characters, %d string long given', strlen($code))
        );
        Assert::regex(
            $code,
            self::REGULAR_EXPRESSION,
            'Option code may contain only letters, numbers and underscores. %s given',
        );
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
