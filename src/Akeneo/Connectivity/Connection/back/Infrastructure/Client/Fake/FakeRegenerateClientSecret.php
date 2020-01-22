<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Client\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecret;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;
use FOS\OAuthServerBundle\Util\Random;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FakeRegenerateClientSecret implements RegenerateClientSecret
{
    /** @var InMemoryConnectionRepository */
    private $connectionRepository;

    public function __construct(InMemoryConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function execute(ClientId $clientId): void
    {
        foreach ($this->connectionRepository->dataRows as $connectionCode => $connectionData) {
            if ($clientId->id() === (int) $connectionData['client_id']) {
                $this->connectionRepository->dataRows[$connectionCode]['secret'] = Random::generateToken();

                return;
            }
        }

        throw new \InvalidArgumentException(sprintf('Client id "%s" not found!', $clientId->id()));
    }
}
