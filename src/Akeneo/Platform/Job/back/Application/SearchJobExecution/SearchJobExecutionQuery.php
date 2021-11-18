<?php

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecutionQuery
{
    public int $page = 1;
    public int $size = 25;
    public string $sortColumn = 'started_at';
    public string $sortDirection = 'DESC';
    public array $users = [];
    public array $type = [];
    public array $status = [];
    public string $search = '';
}
