<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppAndCredentials
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

    public function __construct(string $code, string $label, string $flowType, string $clientId, string $secret)
    {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->clientId = $clientId;
        $this->secret = $secret;
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
}
