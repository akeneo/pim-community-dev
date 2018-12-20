<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\CanEditReferenceEntityInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryCanEditReferenceEntityStub implements CanEditReferenceEntityInterface
{
    private $isAllowed = true;

    public function __invoke(SecurityIdentifier $securityIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        return $this->isAllowed;
    }

    public function forbid(): void
    {
        $this->isAllowed = false;
    }

    public function allow(): void
    {
        $this->isAllowed = true;
    }
}
