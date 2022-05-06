<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Api\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAuthenticationEvent extends Event
{
    /** @var string */
    private $username;

    /** @var string */
    private $clientId;

    public function __construct(string $username, string $clientId)
    {
        $this->username = $username;
        $this->clientId = $clientId;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }
}
