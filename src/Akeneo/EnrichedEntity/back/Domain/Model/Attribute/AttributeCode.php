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

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeCode
{
    /** @var string */
    private $code;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'Attribute code cannot be empty');
        Assert::maxLength(
            $identifier,
            255,
            sprintf('Attribute code cannot be longer than 255 characters, %d string long given', strlen($identifier))
        );
        Assert::regex(
            $identifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf('Attribute code may contain only letters, numbers and underscores. "%s" given', $identifier)
        );

        $this->code = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
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
