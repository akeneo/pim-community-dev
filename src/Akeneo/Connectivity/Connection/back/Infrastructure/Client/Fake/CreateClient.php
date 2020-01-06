<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Client\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateClient implements CreateClientInterface
{
    public function execute(string $label): Client
    {
        return new Client(806, '806_public_id', 'my_secret');
    }
}
