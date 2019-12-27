<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionWithCredentials
{
    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var string */
    private $flowType;

    /** @var string */
    private $clientId;

    /** @var string */
    private $secret;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string|null */
    private $image;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        string $clientId,
        string $secret,
        string $username,
        ?string $password = null,
        ?string $image = null
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->image = $image;
        $this->username = $username;
        $this->password = $password;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function flowType(): string
    {
        return $this->flowType;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function image(): ?string
    {
        return $this->image;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'flow_type' => $this->flowType,
            'image' => $this->image,
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
