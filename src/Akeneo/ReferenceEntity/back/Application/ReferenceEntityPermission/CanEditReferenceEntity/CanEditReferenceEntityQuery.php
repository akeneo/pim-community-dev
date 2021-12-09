<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQuery
{
    public function __construct(
        public string $referenceEntityIdentifier,
        public string $securityIdentifier
    ) {
    }
}
