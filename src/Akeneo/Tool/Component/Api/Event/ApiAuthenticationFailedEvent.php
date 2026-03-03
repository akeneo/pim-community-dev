<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Api\Event;

use OAuth2\OAuth2AuthenticateException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAuthenticationFailedEvent extends Event
{
    public function __construct(private OAuth2AuthenticateException $exception, private string $token)
    {
    }

    public function getException(): OAuth2AuthenticateException
    {
        return $this->exception;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
