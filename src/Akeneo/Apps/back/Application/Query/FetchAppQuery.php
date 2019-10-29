<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppQuery
{
    /** @var string */
    private $appCode;

    public function __construct(string $appCode)
    {
        $this->appCode = $appCode;
    }

    public function appCode(): string
    {
        return $this->appCode;
    }
}
