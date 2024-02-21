<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Connection
{
    private ConnectionCode $code;

    private ConnectionLabel $label;

    private FlowType $flowType;

    private ClientId $clientId;

    private UserId $userId;

    private ?ConnectionImage $image;

    private ConnectionType $type;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        int $clientId,
        int $userId,
        ?string $image = null,
        private bool $auditable = false,
        ?string $type = null
    ) {
        $this->code = new ConnectionCode($code);
        $this->label = new ConnectionLabel($label);
        $this->flowType = new FlowType($flowType);
        $this->clientId = new ClientId($clientId);
        $this->userId = new UserId($userId);
        $this->image = null !== $image ? new ConnectionImage($image) : null;
        $this->type = new ConnectionType($type);
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

    public function auditable(): bool
    {
        return $this->auditable;
    }

    public function type(): ConnectionType
    {
        return $this->type;
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

    public function enableAudit(): void
    {
        $this->auditable = true;
    }

    public function disableAudit(): void
    {
        $this->auditable = false;
    }
}
