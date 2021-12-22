<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Connection
{
    private string $code;

    private string $label;

    private string $flowType;

    private ?string $image;

    private bool $auditable;

    private string $type;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        ?string $image = null,
        bool $auditable = false,
        string $type = ConnectionType::DEFAULT_TYPE
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->image = $image;
        $this->auditable = $auditable;
        $this->type = $type;
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

    public function image(): ?string
    {
        return $this->image;
    }

    public function auditable(): bool
    {
        return $this->auditable;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array{
     *  code: string,
     *  label: string,
     *  flowType: string,
     *  image: ?string,
     *  auditable: bool,
     *  type: string,
     * }
     */
    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'flowType' => $this->flowType,
            'image' => $this->image,
            'auditable' => $this->auditable,
            'type' => $this->type,
        ];
    }
}
