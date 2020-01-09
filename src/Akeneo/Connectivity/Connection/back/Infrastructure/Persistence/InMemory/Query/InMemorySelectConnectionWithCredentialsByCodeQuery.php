<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemorySelectConnectionWithCredentialsByCodeQuery implements SelectConnectionWithCredentialsByCodeQuery
{
    /** @var InMemoryConnectionRepository */
    private $connectionRepository;

    public function __construct(InMemoryConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function execute(string $code): ?ConnectionWithCredentials
    {
        $dataRows = $this->connectionRepository->dataRows;

        if (!isset($dataRows[$code])) {
            return null;
        }

        $dataRow = $dataRows[$code];

        return new ConnectionWithCredentials(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['client_id'] .'_'. $dataRow['random_id'],
            $dataRow['secret'],
            $dataRow['code'] .'_app',
            null,
            $dataRow['image'],
        );
    }
}
