<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetTableValue implements ValueUserIntent
{
    /**
     * @param array<int, array<string, mixed>> $tableValue
     *     ex. [
     *             0 => ['ingredient' => 'butter', 'quantity' => 2],
     *             1 => ['ingredient' => 'salt'],
     *         ]
     */
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private array $tableValue
    ) {
        Assert::isList($tableValue);
        Assert::allIsArray($tableValue);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * @return array<int, array<string, mixed>>
     *     ex. [
     *             0 => ['ingredient' => 'butter', 'quantity' => 2],
     *             1 => ['ingredient' => 'salt'],
     *         ]
     */
    public function tableValue(): array
    {
        return $this->tableValue;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }
}
