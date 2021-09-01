<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AppAuthorizationSessionInterface
{
    public function initialize(AppAuthorization $authorization): void;
    public function getAppAuthorization(string $clientId): ?AppAuthorization;
}
