<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindValueKeysByAttributeTypeInterface
{
    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     * @param string[]                  $attributeTypes
     *
     * @return string[]
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array;
}
