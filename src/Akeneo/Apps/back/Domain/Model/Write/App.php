<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Write;

use Akeneo\Apps\Domain\Model\ValueObject\AppId;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class App
{
    /** @var AppId */
    private $id;

    /** @var AppCode */
    private $code;

    /** @var AppLabel */
    private $label;

    /** @var FlowType */
    private $flowType;

    /** @var ClientId */
    private $clientId;

    private function __construct(AppId $id, AppCode $code, AppLabel $label, FlowType $flowType, ClientId $clientId)
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->clientId = $clientId;
    }

    public static function create(string $id, string $appCode, string $label, string $flowType, ClientId $clientId): self
    {
        // TODO: Validation + Id Generation

        return new self(
            new AppId($id),
            new AppCode($appCode),
            new AppLabel($label),
            new FlowType($flowType),
            $clientId
        );
    }

    public function id(): AppId
    {
        return $this->id;
    }

    public function code(): AppCode
    {
        return $this->code;
    }

    public function label(): AppLabel
    {
        return $this->label;
    }

    public function flowType(): FlowType
    {
        return $this->flowType;
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }
}
