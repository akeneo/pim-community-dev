<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RefreshConnectedAppCommand
{
    public function __construct(private string $appId)
    {
    }

    public function getAppId(): string
    {
        return $this->appId;
    }
}
