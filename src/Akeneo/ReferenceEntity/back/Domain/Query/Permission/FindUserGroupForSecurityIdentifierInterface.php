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

namespace Akeneo\ReferenceEntity\Domain\Query\Permission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;

interface FindUserGroupForSecurityIdentifierInterface
{
    public function __invoke(SecurityIdentifier $securityIdentifier): ?UserGroupIdentifier;
}
