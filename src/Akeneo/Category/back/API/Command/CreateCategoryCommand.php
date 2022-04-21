<?php

declare(strict_types=1);

namespace Akeneo\Category\API\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCategoryCommand
{
    private function __construct(
        private string  $code,
        private ?string $parent,
        private array   $labels,
    ) {
    }

    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'code');
        Assert::string($data['code']);

        $parent = $data['parent'] ?? null;
        Assert::nullOrString($parent);

        $labels = $data['labels'] ?? [];
        Assert::isArray($labels);

        Assert::allString(\array_keys($labels));
        Assert::allString(\array_values($labels));

        return new self($data['code'], $parent, $labels);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
