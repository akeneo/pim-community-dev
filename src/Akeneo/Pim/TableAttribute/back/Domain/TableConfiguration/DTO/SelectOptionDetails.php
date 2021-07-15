<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO;

final class SelectOptionDetails
{
    private string $attributeCode;
    private string $columnCode;
    private string $optionCode;
    /** @var array<string, string> */
    private array $labels;

    /**
     * @param array<string, string> $labels
     */
    public function __construct(
        string $attributeCode,
        string $columnCode,
        string $optionCode,
        array $labels
    ) {
        $this->attributeCode = $attributeCode;
        $this->columnCode = $columnCode;
        $this->optionCode = $optionCode;
        $this->labels = $labels;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function columnCode(): string
    {
        return $this->columnCode;
    }

    public function optionCode(): string
    {
        return $this->optionCode;
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return $this->labels;
    }
}
