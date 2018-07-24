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
        Assert::stringNotEmpty($identifier);
        Assert::maxLength($identifier, 255);
        if (1 !== preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException('Record identifier may contain only letters, numbers and underscores');
        }

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

    public function equals(RecordCode $identifier): bool
    {
        return $this->code === $identifier->code;
    }
}
