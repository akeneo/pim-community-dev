<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webhook
{
    /** @var string */
    private $connectionCode;

    /** @var string */
    private $userGroup;

    /** @var string */
    private $userRole;

    /** @var string */
    private $secret;

    /** @var string */
    private $url;

    public function __construct(
        string $connectionCode,
        string $userGroup,
        string $userRole,
        string $secret,
        string $url
    ) {
        $this->connectionCode = $connectionCode;
        $this->userGroup = $userGroup;
        $this->userRole = $userRole;
        $this->secret = $secret;
        $this->url = $url;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function userGroup(): string
    {
        return $this->userGroup;
    }

    public function userRole(): string
    {
        return $this->userRole;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function url(): string
    {
        return $this->url;
    }
}
