<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQuery
{
    /** @var string */
    private $referenceEntityIdentifier;

    /** @var string */
    private $securityIdentifier;

    public function __construct(string $referenceEntityIdentifier, string $securityIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->securityIdentifier = $securityIdentifier;
    }

    public function getReferenceEntityIdentifier(): string
    {
        return $this->referenceEntityIdentifier;
    }

    public function getSecurityIdentifier(): string
    {
        return $this->securityIdentifier;
    }
}
