<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetConnectionBusinessErrorsQuery
{
    public function __construct(private string $connectionCode, private ?string $endDate = null)
    {
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function endDate(): ?string
    {
        return $this->endDate;
    }
}
