<?php

namespace Akeneo\Platform\Job\Application\SearchJobExecutionTable;

use Akeneo\Platform\Job\Domain\Query\SearchExecutionTableQueryInterface;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchExecutionTableQuery implements SearchExecutionTableQueryInterface
{
    public int $page = 1;
    public int $size = 25;

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }


}
