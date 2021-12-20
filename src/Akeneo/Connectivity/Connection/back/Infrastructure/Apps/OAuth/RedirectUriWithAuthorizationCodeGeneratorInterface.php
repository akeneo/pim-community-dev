<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;

interface RedirectUriWithAuthorizationCodeGeneratorInterface
{
    public function generate(
        AppAuthorization $appAuthorization,
        AppConfirmation $appConfirmation,
        int $pimUserId
    ): string;
}
