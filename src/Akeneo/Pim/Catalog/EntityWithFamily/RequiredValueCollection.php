<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * A collection of required values depending on the attribute requirements of a family.
 * {@see Pim\Component\Catalog\EntityWithFamily\RequiredValue}
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
class RequiredValueCollection implements \Countable, \IteratorAggregate
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
     * Returns all the elements of this collection that satisfy the given channel and locale.
     *
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return RequiredValueCollection
     */
    public function filterByChannelAndLocale(
        ChannelInterface $channel,
        LocaleInterface $locale
    ): RequiredValueCollection {
        $filteredValues = array_filter(
            $this->values,
            function (ValueInterface $value) use ($channel, $locale) {
                $attribute = $value->getAttribute();

                if ($attribute->isScopable() && $channel->getCode() !== $value->getScope()) {
                    return false;
                }

                if ($attribute->isLocalizable() && $locale->getCode() !== $value->getLocale()) {
                    return false;
                }

                return true;
            }
        );

        return new static($filteredValues);
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
        $channelCode = null !== $value->getScope() ? $value->getScope() : '<all_channels>';
        $localeCode = null !== $value->getLocale() ? $value->getLocale() : '<all_locales>';
        $key = sprintf('%s-%s-%s', $attribute->getCode(), $channelCode, $localeCode);

        return $key;
    }
}
