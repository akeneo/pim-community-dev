<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Connection
{
    /** @var ConnectionCode */
    private $code;

    /** @var ConnectionLabel */
    private $label;

    /** @var FlowType */
    private $flowType;

    /** @var ClientId */
    private $clientId;

    /** @var UserId */
    private $userId;

    /** @var ConnectionImage|null */
    private $image;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        int $clientId,
        UserId $userId,
        ?string $image = null
    ) {
        $this->code = new ConnectionCode($code);
        $this->label = new ConnectionLabel($label);
        $this->flowType = new FlowType($flowType);
        $this->clientId = new ClientId($clientId);
        $this->userId = $userId;
        $this->image = null !== $image ? new ConnectionImage($image) : null;
    }

    public function code(): ConnectionCode
    {
        return $this->code;
    }

    public function label(): ConnectionLabel
    {
        return $this->label;
    }

    public function flowType(): FlowType
    {
        return $this->flowType;
    }

    public function image(): ?ConnectionImage
    {
        return $this->image;
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function setLabel(ConnectionLabel $label): void
    {
        $this->label = $label;
    }

    public function setFlowType(FlowType $flowType): void
    {
        $this->flowType = $flowType;
    }

    public function setImage(?ConnectionImage $image): void
    {
        $this->image = $image;
    }
}
