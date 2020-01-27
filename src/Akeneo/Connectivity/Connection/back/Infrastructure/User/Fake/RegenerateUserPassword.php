<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\User\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPassword as RegenerateUserPasswordService;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegenerateUserPassword implements RegenerateUserPasswordService
{
    /** @var InMemoryConnectionRepository */
    private $connectionRepository;

    public function __construct(InMemoryConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function execute(UserId $userId): string
    {
        foreach ($this->connectionRepository->dataRows as $connectionCode => $connectionData) {
            if ($userId->id() === (int) $connectionData['user_id']) {
                $newPassword = uniqid('pwd_');
                $this->connectionRepository->dataRows[$connectionCode]['password'] = $newPassword;

                return $newPassword;
            }
        }

        throw new \InvalidArgumentException(sprintf('User id "%s" not found!', $userId->id()));
    }
}
