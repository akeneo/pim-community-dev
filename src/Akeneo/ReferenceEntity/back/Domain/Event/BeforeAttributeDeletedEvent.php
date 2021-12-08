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

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @internal
 */
class BeforeAttributeDeletedEvent extends Event
{
    public function __construct(
        public ReferenceEntityIdentifier $referenceEntityIdentifier,
        public AttributeIdentifier $attributeIdentifier
    ) {
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    public function getAttributeIdentifier(): AttributeIdentifier
    {
        return $this->attributeIdentifier;
    }
}
