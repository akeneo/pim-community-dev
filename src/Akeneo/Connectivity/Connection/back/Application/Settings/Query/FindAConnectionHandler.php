<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQueryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionHandler
{
    public function __construct(private SelectConnectionWithCredentialsByCodeQueryInterface $selectConnectionWithCredentialsByCodeQuery)
    {
    }

    public function handle(FindAConnectionQuery $query): ?ConnectionWithCredentials
    {
        return $this->selectConnectionWithCredentialsByCodeQuery->execute($query->connectionCode());
    }
}
