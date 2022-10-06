<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * The collection of the generator labels by locale
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LabelCollection
{
    /**
     * @param array<string, string> $labels
     */
    public function __construct(
        private array $labels,
    ) {
    }

    /**
     * @param array<string, string>|\stdClass $normalizedLabels
     */
    public static function fromNormalized($normalizedLabels): self
    {
        if ($normalizedLabels instanceof \stdClass) {
            $normalizedLabels = [];
        }
        Assert::isArray($normalizedLabels);
        Assert::allString($normalizedLabels);
        Assert::allStringNotEmpty(\array_keys($normalizedLabels));

        return new self(\array_filter($normalizedLabels));
    }

    /**
     * @return array<string, string>|\stdClass
     */
    public function normalize()
    {
        return [] === $this->labels ? (object) [] : $this->labels;
    }

    /**
     * @param array<string, string> $labels
     */
    public function merge(array $labels): self
    {
        return LabelCollection::fromNormalized(\array_replace($this->labels, $labels));
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labels[$localeCode] ?? null;
    }
}
