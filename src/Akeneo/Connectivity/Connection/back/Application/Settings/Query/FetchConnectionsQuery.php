<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FetchConnectionsQuery
{
    /** @var array<string, mixed> */
    private array $types;

    /**
     * @param array<string, mixed> $search
     */
    public function __construct(array $search)
    {
        $this->types = $search['types'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
