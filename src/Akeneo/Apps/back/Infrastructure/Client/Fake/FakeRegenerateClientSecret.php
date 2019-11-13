<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Client\Fake;

use Akeneo\Apps\Application\Service\RegenerateClientSecret;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FakeRegenerateClientSecret implements RegenerateClientSecret
{
    public function execute(ClientId $clientId): void
    {
        // Do nothing. Simulate external deletion in the database
    }
}
