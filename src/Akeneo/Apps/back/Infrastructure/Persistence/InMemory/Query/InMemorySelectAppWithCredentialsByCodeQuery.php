<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\InMemory\Query;

use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppWithCredentialsByCodeQuery;
use Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository\InMemoryAppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemorySelectAppWithCredentialsByCodeQuery implements SelectAppWithCredentialsByCodeQuery
{
    /** @var InMemoryAppRepository */
    private $appRepository;

    public function __construct(InMemoryAppRepository $appRepository)
    {
        $this->appRepository = $appRepository;
    }

    public function execute(string $code): ?AppWithCredentials
    {
        $dataRows = $this->appRepository->dataRows;

        if (!isset($dataRows[$code])) {
            return null;
        }

        $dataRow = $dataRows[$code];

        return new AppWithCredentials(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['random_id'],
            $dataRow['secret'],
        );
    }
}
