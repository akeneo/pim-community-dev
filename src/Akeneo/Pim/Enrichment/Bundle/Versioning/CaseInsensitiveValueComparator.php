<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeCodes;
use Akeneo\Tool\Component\Versioning\Model\ValueComparatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CaseInsensitiveValueComparator implements ValueComparatorInterface
{
    /** @var array<string, int>|null */
    private ?array $indexedSupportedFields = null;

    public function __construct(private GetAttributeCodes $getAttributeCodes, private array $attributeTypes)
    {
        Assert::allString($this->attributeTypes);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedResourceNames(): array
    {
        return [Product::class, ProductModel::class];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsField(string $field): bool
    {
        if (null === $this->indexedSupportedFields) {
            $this->indexedSupportedFields = \array_flip(
                $this->getAttributeCodes->forAttributeTypes($this->attributeTypes)
            );
        }

        $pos = \strpos($field, '-');
        if (false !== $pos) {
            $field = substr($field, 0, $pos);
        }

        return \array_key_exists($field, $this->indexedSupportedFields);
    }

    /**
     * {@inheritDoc}
     */
    public function isEqual($value1, $value2): bool
    {
        if (\is_string($value1) && \is_string($value2)) {
            return \strtolower($value1) === \strtolower($value2);
        }

        return $value1 === $value2;
    }
}
