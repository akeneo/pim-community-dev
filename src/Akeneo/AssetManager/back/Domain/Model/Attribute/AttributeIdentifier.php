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
class AttributeIdentifier
{
    private string $identifier;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'Attribute identifier cannot be empty');
        Assert::maxLength(
            $identifier,
            255,
            sprintf(
                'Attribute identifier cannot be longer than 255 characters, %d string long given',
                strlen($identifier)
            )
        );
        Assert::regex(
            $identifier,
            '/^[a-zA-Z0-9_-]+$/',
            sprintf(
                'Attribute identifier may contain only letters, numbers, underscores and dashes. "%s" given',
                $identifier
            )
        );

        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public static function create(string $assetFamilyIdentifier, string $attributeCode, string $fingerprint): self
    {
        Assert::stringNotEmpty($assetFamilyIdentifier, 'Asset family identifier cannot be empty');
        Assert::regex(
            $assetFamilyIdentifier,
            '/^[a-zA-Z0-9_]+$/',
            sprintf(
                'Asset family identifier may contain only letters, numbers and underscores. "%s" given',
                $attributeCode
            )
        );

        Assert::stringNotEmpty($attributeCode, 'Attribute code cannot be empty');
        Assert::regex(
            $attributeCode,
            '/^[a-zA-Z0-9_]+$/',
            sprintf(
                'Attribute code may contain only letters, numbers and underscores. "%s" given',
                $attributeCode
            )
        );

        Assert::stringNotEmpty($fingerprint, 'Fingerprint cannot be empty');
        Assert::regex(
            $fingerprint,
            '/^[a-zA-Z0-9_-]+$/',
            sprintf(
                'Fingerprint may contain only letters, numbers, underscores and dashes. "%s" given',
                $fingerprint
            )
        );

        return new self(
            sprintf(
                '%s_%s_%s',
                substr($attributeCode, 0, 20),
                substr($assetFamilyIdentifier, 0, 20),
                $fingerprint
            )
        );
    }

    public function equals(AttributeIdentifier $identifier): bool
    {
        return $this->identifier === $identifier->identifier;
    }

    public function normalize(): string
    {
        return $this->identifier;
    }

    public function stringValue(): string
    {
        return $this->identifier;
    }

    public function __toString(): string
    {
        return $this->stringValue();
    }
}
