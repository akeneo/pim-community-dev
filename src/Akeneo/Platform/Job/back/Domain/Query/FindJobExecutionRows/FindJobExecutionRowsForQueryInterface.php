<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Domain\Query\FindJobExecutionRows;

use Akeneo\Platform\Job\Domain\Query\SearchExecutionTableQueryInterface;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindJobExecutionRowsForQueryInterface
{
    public function find(SearchExecutionTableQueryInterface $query): FindJobExecutionRowsResult;
}
