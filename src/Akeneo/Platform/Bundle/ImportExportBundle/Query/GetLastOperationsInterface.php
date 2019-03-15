<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Get last 10 job executions for the dashboard widget.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetLastOperationsInterface
{
    /**
     * Get data for the last operations widget.
     *
     * @param UserInterface $user
     *
     * @return array
     */
    public function execute(UserInterface $user): array;

    /**
     * @param UserInterface $user
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(UserInterface $user): QueryBuilder;
}
