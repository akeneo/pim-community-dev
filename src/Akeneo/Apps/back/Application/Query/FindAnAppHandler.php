<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Query;

use Akeneo\Apps\Domain\Model\Read\AppAndCredentials;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppAndCredentialsByCodeQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAnAppHandler
{
    /** @var SelectAppAndCredentialsByCodeQuery */
    private $selectAppAndCredentialsByCodeQuery;

    public function __construct(SelectAppAndCredentialsByCodeQuery $selectAppAndCredentialsByCodeQuery)
    {
        $this->selectAppAndCredentialsByCodeQuery = $selectAppAndCredentialsByCodeQuery;
    }

    public function handle(FindAnAppQuery $query): ?AppAndCredentials
    {
        return $this->selectAppAndCredentialsByCodeQuery->execute($query->appCode());
    }
}
