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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;

/**
 * An AttributeAsImageReference represents an attribute used as main image for a Reference Entity.
 *
 * If there is an attribute, then the AttributeAsImageReference is the AttributeIdentifier of the attribute
 * If there is no attribute then it is null
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AttributeAsImageReference
{
    /** @var AttributeIdentifier|null */
    private $identifier;

    private function __construct(?AttributeIdentifier $attributeIdentifier)
    {
        $this->identifier = $attributeIdentifier;
    }

    public static function fromAttributeIdentifier(AttributeIdentifier $identifier): self
    {
        return new self($identifier);
    }

    public static function noReference(): self
    {
        return new self(null);
    }

    public static function createFromNormalized(?string $nomalizedIdentifier): self
    {
        if (null === $nomalizedIdentifier) {
            return AttributeAsImageReference::noReference();
        }

        return self::fromAttributeIdentifier(AttributeIdentifier::fromString($nomalizedIdentifier));
    }

    public function getIdentifier(): AttributeIdentifier
    {
        return $this->identifier;
    }

    public function normalize(): ?string
    {
        if (null === $this->identifier) {
            return null;
        }

        return $this->identifier->normalize();
    }

    public function isEmpty(): bool
    {
        return null === $this->identifier;
    }
}
