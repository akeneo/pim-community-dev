<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClientProviderInterface
{
    /**
     * Provides client given App identifier, creates a new one if none found
     * @param $app
     * @return Client
     */
    public function findOrCreateClient($app): Client;
}
