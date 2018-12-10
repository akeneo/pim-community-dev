<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Permission\CanEditReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Permission\UserIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryCanEditReferenceEntity implements CanEditReferenceEntityInterface
{
    public function __invoke(UserIdentifier $userIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        return false;
    }
}
