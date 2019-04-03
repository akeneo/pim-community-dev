<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;

/**
 * A collection of required values depending on the attribute requirements of a family.
 * {@see Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValue}
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
    /** @var RequiredValue[] */
    private $values;

    /**
     * @param RequiredValue[] $values
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
            function (RequiredValue $requiredValue) use ($channel, $locale) {
                $attribute = $requiredValue->forAttribute();

                if ($attribute->isScopable() && $requiredValue->forChannel()->getCode() !== $channel->getCode()) {
                    return false;
                }

                if ($attribute->isLocalizable() && $requiredValue->forLocale()->getCode() !== $locale->getCode()) {
                    return false;
                }

                if ($attribute->isLocaleSpecific() &&
                    (!$attribute->hasLocaleSpecific($locale) || $requiredValue->forLocale()->getCode() !== $locale->getCode())
                ) {
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

    private function buildInternalKey(RequiredValue $requiredValue): string
    {
        $channelCode = null !== $requiredValue->channel() ? $requiredValue->channel() : '<all_channels>';
        $localeCode = null !== $requiredValue->locale() ? $requiredValue->locale() : '<all_locales>';
        $key = sprintf('%s-%s-%s', $requiredValue->attribute(), $channelCode, $localeCode);

        return $key;
    }
}
