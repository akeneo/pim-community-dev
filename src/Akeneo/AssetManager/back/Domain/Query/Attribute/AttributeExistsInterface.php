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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Query to determine if an Reference Entity Attribute exists
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface AttributeExistsInterface
{
    public function withIdentifier(AttributeIdentifier $identifier): bool;

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $identifier, AttributeCode $attributeCode): bool;

    public function withReferenceEntityIdentifierAndOrder(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeOrder $order): bool;
}
