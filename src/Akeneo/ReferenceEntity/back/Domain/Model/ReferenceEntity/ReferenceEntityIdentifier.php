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

namespace Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityIdentifier
{
    /** @var string */
    private $identifier;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'Reference entity identifier cannot be empty');
        Assert::maxLength(
            $identifier,
            255,
            sprintf(
                'Reference entity identifier cannot be longer than 255 characters, %d string long given',
                strlen($identifier)
            )
        );
        Assert::regex(
            $identifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf(
                'Reference entity identifier may contain only letters, numbers and underscores. "%s" given',
                $identifier
            )
        );

        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function equals(ReferenceEntityIdentifier $identifier): bool
    {
        return $this->identifier === (string) $identifier;
    }

    public function normalize(): string
    {
        return $this->identifier;
    }
}
