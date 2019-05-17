<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * A collection of incomplete values depending on an entity with a family
 * {@see Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface}.
 *
 * An incomplete value collection holds all required values ({@see Pim\Component\Catalog\RequiredValue}) that are
 * either missing or empty in an entity.
 *
 * This collection is not dependant of a channel and/or locale context. Which means, it's the responsibility of
 * the user to know what the collection holds.
 *
 * @internal
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IncompleteValueCollection implements \Countable, \IteratorAggregate
{
    /** @var RequiredValue[] */
    private $values;

    /**
     * @param RequiredValue[] $values
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        $this->values = [];

        foreach ($values as $value) {
            if (!$value instanceof RequiredValue) {
                throw new \InvalidArgumentException(
                    'Expected an instance of "Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValue".'
                );
            }

            $this->values[$this->buildInternalKey($value)] = $value;
        }
    }

    /**
     * Is there already a value with the same attribute, channel and locale than $value?
     *
     * @param RequiredValue $value
     *
     * @return bool
     */
    public function hasSame(RequiredValue $value): bool
    {
        return array_key_exists($this->buildInternalKey($value), $this->values);
    }

    /**
     * Get the list of attributes within those incomplete values
     *
     * @return Collection
     */
    public function attributes(): Collection
    {
        $attributes = new ArrayCollection();

        foreach ($this->values as $value) {
            $attribute = $value->forAttribute();
            if (!$attributes->contains($attribute)) {
                $attributes->add($attribute);
            }
        }

        return $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @param RequiredValue $requiredValue
     *
     * @return string
     */
    private function buildInternalKey(RequiredValue $requiredValue): string
    {
        $channelCode = $requiredValue->channel() ?? '<all_channels>';
        $localeCode = $requiredValue->locale() ?? '<all_locales>';
        $key = sprintf('%s-%s-%s', $requiredValue->attribute(), $channelCode, $localeCode);

        return $key;
    }
}
