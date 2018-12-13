<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Permission\CanEditReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Model\UserIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryCanEditReferenceEntityStub implements CanEditReferenceEntityInterface
{
    private $isAllowed = true;

    public function __invoke(UserIdentifier $userIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
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
