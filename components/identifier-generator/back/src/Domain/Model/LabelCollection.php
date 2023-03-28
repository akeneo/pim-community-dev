<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * The collection of the generator labels by locale
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type LabelsNormalized array<string, string>
 */
final class LabelCollection
{
    /**
     * @param array<string, string> $labels
     */
    public function __construct(
        private readonly array $labels,
    ) {
    }

    /**
     * @param array<string, string> $normalizedLabels
     */
    public static function fromNormalized(array $normalizedLabels): self
    {
        Assert::isArray($normalizedLabels);
        Assert::allString($normalizedLabels);
        Assert::allStringNotEmpty(\array_keys($normalizedLabels));

        return new self(\array_filter($normalizedLabels, static fn (string $label): bool => '' !== \trim($label)));
    }

    /**
     * @return LabelsNormalized
     */
    public function normalize(): array
    {
        return $this->labels;
    }
}
