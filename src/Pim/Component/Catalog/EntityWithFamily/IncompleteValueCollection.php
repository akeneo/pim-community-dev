<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * A collection of incomplete values depending on an entity with a family
 * {@see Pim\Component\Catalog\Model\EntityWithFamilyInterface}.
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
    /** @var ValueInterface[] */
    private $values;

    /**
     * @param ValueInterface[] $values
     */
    public function __construct(array $values)
    {
        $this->values = [];

        foreach ($values as $value) {
            if (!$value instanceof ValueInterface) {
                throw new \InvalidArgumentException(
                    'Expected an instance of "Pim\Component\Catalog\Model\ValueInterface".'
                );
            }

            $this->values[$this->buildInternalKey($value)] = $value;
        }
    }

    /**
     * Is there already a value with the same attribute, channel and locale than $value?
     *
     * @param ValueInterface $value
     *
     * @return bool
     */
    public function hasSame(ValueInterface $value): bool
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
            $attribute = $value->getAttribute();
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
    public function count()
    {
        return count($this->values);
    }

    /**
     * @param ValueInterface $value
     *
     * @return string
     */
    private function buildInternalKey(ValueInterface $value): string
    {
        $attribute = $value->getAttribute();
        $channelCode = null !== $value->getScope() ? $value->getScope() : ' < all_channels>';
        $localeCode = null !== $value->getLocale() ? $value->getLocale() : '<all_locales > ';
        $key = sprintf('%s-%s-%s', $attribute->getCode(), $channelCode, $localeCode);

        return $key;
    }
}
