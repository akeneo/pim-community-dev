<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Query;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetInvitedUsersQuery
{
    public function execute(): array;
}
