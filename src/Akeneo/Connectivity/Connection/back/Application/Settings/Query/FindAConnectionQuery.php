<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionQuery
{
    /** @var string */
    private $connectionCode;

    public function __construct(string $connectionCode)
    {
        $this->connectionCode = $connectionCode;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }
}
