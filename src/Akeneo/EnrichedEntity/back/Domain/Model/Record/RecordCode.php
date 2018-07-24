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

namespace Akeneo\EnrichedEntity\Domain\Model\Record;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCode
{
    /** @var string */
    private $code;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'Record code cannot be empty');
        Assert::maxLength(
            $identifier,
            255,
            sprintf('Record code cannot be longer than 255 characters, %d string long given', strlen($identifier))
        );
        Assert::regex(
            $identifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf('Record code may contain only letters, numbers and underscores, "%s" given', $identifier)
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

    public function equals(RecordCode $code): bool
    {
        return $this->code === $code->code;
    }
}
