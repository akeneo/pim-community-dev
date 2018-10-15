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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AttributeReferenceEntityIdentifier
{
    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    private function __construct(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }

    public static function fromReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): self
    {
        return new self($referenceEntityIdentifier);
    }

    public function __toString(): string
    {
        return (string) $this->referenceEntityIdentifier;
    }

    public function normalize(): string
    {
        return $this->referenceEntityIdentifier->normalize();
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }
}
