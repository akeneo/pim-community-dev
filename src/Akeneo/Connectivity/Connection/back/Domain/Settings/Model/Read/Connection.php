<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

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

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        ?string $image = null,
        bool $auditable = false
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->image = $image;
        $this->auditable = $auditable;
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

    /**
     * @return array{
     *  code: string,
     *  label: string,
     *  flowType: string,
     *  image: ?string,
     *  auditable: bool
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
        ];
    }
}
