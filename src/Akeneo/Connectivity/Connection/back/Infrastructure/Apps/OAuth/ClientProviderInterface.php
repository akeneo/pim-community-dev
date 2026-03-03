<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClientProviderInterface
{
    /**
     * Provides client given App identifier, creates a new one if none found
     * @param App $app
     * @return Client
     */
    public function findOrCreateClient(App $app): Client;

    public function findClientByAppId(string $appId): ?Client;
}
