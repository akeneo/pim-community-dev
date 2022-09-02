<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectLastConnectionBusinessErrorsQueryInterface
{
    /**
     * Select the connection business errors between the $endDate and 7 days before $endDate.
     *
     * @param string|null $endDate Format 'Y-m-d'
     *
     * @return BusinessError[]
     */
    public function execute(string $connectionCode, string $endDate = null, int $limit = 100): array;
}
