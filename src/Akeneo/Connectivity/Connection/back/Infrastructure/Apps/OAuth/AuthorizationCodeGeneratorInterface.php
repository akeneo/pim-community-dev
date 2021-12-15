<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;

interface AuthorizationCodeGeneratorInterface
{
    public function generate(AppConfirmation $appConfirmation, int $pimUserId, string $redirectUriWithoutCode): string;
}
