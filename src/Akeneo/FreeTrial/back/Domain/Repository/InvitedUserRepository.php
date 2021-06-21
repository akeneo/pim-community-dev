<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Repository;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface InvitedUserRepository
{
    public function save(InvitedUser $invitedUser): void;
}