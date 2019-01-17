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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface FindReferenceEntityAttributeAsImageInterface
{
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsImageReference;
}
