<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Client
{
    /** @var int */
    private $id;

    /** @var string */
    private $clientId;

    /** @var string */
    private $secret;

    public function __construct(int $id, string $clientId, string $secret)
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function secret(): string
    {
        return $this->secret;
    }
}
