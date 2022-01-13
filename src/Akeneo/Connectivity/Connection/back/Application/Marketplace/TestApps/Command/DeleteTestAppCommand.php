<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppCommand
{
    public function __construct(private string $testAppId)
    {
    }

    public function getTestAppId(): string
    {
        return $this->testAppId;
    }
}
