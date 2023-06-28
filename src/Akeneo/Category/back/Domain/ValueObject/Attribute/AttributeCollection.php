<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCollection implements \Countable
{
    /**
     * @param array<Attribute> $attributes
     */
    private function __construct(private array $attributes)
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);
    }

    /**
     * @param array<Attribute> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    /**
     * @return array<Attribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve an Attribute by his identifier.
     *
     * @param string $identifier format expected : 'code|uuid' (example : title|69e251b3-b876-48b5-9c09-92f54bfb528d)
     */
    public function getAttributeByIdentifier(string $identifier): ?Attribute
    {
        $attribute = array_filter(
            $this->attributes,
            static function ($attribute) use ($identifier) {
                return $attribute->getIdentifier() === $identifier;
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return reset($attribute);
    }

    public function getAttributeByUuid(string $uuid): ?Attribute
    {
        $attribute = array_filter(
            $this->attributes,
            static function ($attribute) use ($uuid) {
                return (string) $attribute->getUuid() === $uuid;
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return reset($attribute);
    }

    public function getAttributeByCode(string $code): ?Attribute
    {
        $attribute = array_filter(
            $this->attributes,
            static function ($attribute) use ($code) {
                return (string) $attribute->getCode() === $code;
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return reset($attribute);
    }

    public function addAttribute(Attribute $attribute): self
    {
        $this->attributes[] = $attribute;

        return new self($this->attributes);
    }

    /**
     * @return array<int, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            static fn (Attribute $attribute) => $attribute->normalize(),
            $this->attributes,
        );
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * Sort attributes based on their order property, then build a new object with the re-indexed attributes.
     * ex. attributes with order properties 1, 3, 7 will be re-indexed as follow: [1, 3, 7] => [1, 2, 3].
     */
    public function rebuildWithIndexedAttributes(): self
    {
        $attributeList = $this->attributes;

        usort(
            $attributeList,
            static function (Attribute $a, Attribute $b) {
                return $a->getOrder()->intValue() - $b->getOrder()->intValue();
            },
        );
        $reindexedAttributeList = [];
        array_walk(
            $attributeList,
            static function ($attribute, $index) use (&$reindexedAttributeList) {
                $newOrder = $index + 1;
                $reindexedAttributeList[$newOrder] = Attribute::fromType(
                    $attribute->getType(),
                    $attribute->getUuid(),
                    $attribute->getCode(),
                    AttributeOrder::fromInteger($newOrder),
                    $attribute->isRequired(),
                    $attribute->isScopable(),
                    $attribute->isLocalizable(),
                    $attribute->getLabelCollection(),
                    $attribute->getTemplateUuid(),
                    $attribute->getAdditionalProperties(),
                );
            },
        );

        return new self($reindexedAttributeList);
    }

    /**
     * Determines the order value that a new attribute would have if it was added to the attributes.
     */
    public function calculateNextOrder(): int
    {
        return 1 + array_reduce(
            $this->attributes,
            static function (int $maxOrder, Attribute $attribute) {
                $attributeOrder = $attribute->getOrder()->intValue();

                return max($attributeOrder, $maxOrder);
            },
            1,
        );
    }

    /**
     * @param array<string> $orderedAttributeUuids
     */
    public function reorder(array $orderedAttributeUuids): void
    {
        $order = 1;
        // We reorder the attributes that are in the ordered list,
        // ignoring the attributes that are not in the collection. Co-edition use case
        foreach ($orderedAttributeUuids as $attributeUuid) {
            $attribute = $this->getAttributeByUuid($attributeUuid);
            if (null === $attribute) {
                continue;
            }
            $attribute->setOrder(AttributeOrder::fromInteger($order++));
        }
        // We reorder the attributes that are not in the ordered list by adding them at the end
        foreach ($this->attributes as $attribute) {
            if (!in_array((string) $attribute->getUuid(), $orderedAttributeUuids, true)) {
                $attribute->setOrder(AttributeOrder::fromInteger($order++));
            }
        }
    }
}
