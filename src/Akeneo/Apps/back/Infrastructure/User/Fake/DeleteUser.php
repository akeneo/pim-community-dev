<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\User\Fake;

use Akeneo\Apps\Application\Service\DeleteUserInterface;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteUser implements DeleteUserInterface
{
    public function execute(UserId $userId): void
    {
        return;
    }
}
