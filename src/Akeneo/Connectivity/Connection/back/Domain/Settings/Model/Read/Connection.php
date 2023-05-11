<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Connection
{
    public function __construct(
        private string $code,
        private string $label,
        private string $flowType,
        private ?string $image = null,
        private bool $auditable = false,
        private string $type = ConnectionType::DEFAULT_TYPE
    ) {
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
