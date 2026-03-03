<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongCredentialsCombination
{
    public function __construct(private string $connectionCode, private string $username)
    {
    }

    public function username(): string
    {
        return $this->username;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }
}
