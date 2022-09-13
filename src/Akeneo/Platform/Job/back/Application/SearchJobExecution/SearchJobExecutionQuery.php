<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchJobExecutionQuery
{
    public const MAX_PAGE_WITHOUT_FILTER = 50;

    public static array $supportedSortColumns = [
        'job_name',
        'type',
        'started_at',
        'username',
        'status',
    ];

    public static array $supportedSortDirections = [
        'ASC',
        'DESC',
    ];

    public int $page = 1;
    public int $size = 25;
    public string $sortColumn = 'started_at';
    public string $sortDirection = 'DESC';
    public array $user = [];
    public ?bool $automation = null;
    public array $type = [];
    public array $status = [];
    public array $code = [];
    public string $search = '';
}
