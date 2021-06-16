<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\API;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface InviteUserAPI
{
    public function inviteUser(string $email): void;
}