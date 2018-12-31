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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Get the identifier of an existing attribute from its code and the reference entity identifier
 */
interface GetAttributeIdentifierInterface
{
    /**
     * @return AttributeIdentifier
     *
     * @throws \LogicException if the attribute identifier is not found
     */
    public function withReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier;
}
