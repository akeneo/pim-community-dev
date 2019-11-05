<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Query;

use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppWithCredentialsByCodeQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAnAppHandler
{
    /** @var SelectAppWithCredentialsByCodeQuery */
    private $selectAppWithCredentialsByCodeQuery;

    public function __construct(SelectAppWithCredentialsByCodeQuery $selectAppWithCredentialsByCodeQuery)
    {
        $this->selectAppWithCredentialsByCodeQuery = $selectAppWithCredentialsByCodeQuery;
    }

    public function handle(FindAnAppQuery $query): ?AppWithCredentials
    {
        return $this->selectAppWithCredentialsByCodeQuery->execute($query->appCode());
    }
}
