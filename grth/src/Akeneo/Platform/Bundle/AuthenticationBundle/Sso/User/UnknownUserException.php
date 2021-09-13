<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

/**
 * Thrown when the user provisioning is disabled by the PIM admin and the user trying to authenticate via the IdP
 * is unknown in the PIM.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class UnknownUserException extends \RuntimeException
{
    /** @var string */
    private $username;

    public function __construct(string $username, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->username = $username;

        parent::__construct($message, $code, $previous);
    }
}
