<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOption
{
    private string $code;
    private array $labels;

    public function __construct(string $code, array $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'labels' => $this->labels,
        ];
    }
}
