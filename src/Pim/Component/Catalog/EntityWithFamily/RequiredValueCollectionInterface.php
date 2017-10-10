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
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface RequiredValueCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * Is there already a value with the same attribute, channel and locale than $value?
     *
     * @param ValueInterface $value
     *
     * @return bool
     */
    public function hasSame(ValueInterface $value): bool;

    /**
     * Returns all the elements of this collection that satisfy the given channel and locale.
     *
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return RequiredValueCollectionInterface
     */
    public function filterByChannelAndLocale(
        ChannelInterface $channel,
        LocaleInterface $locale
    ): RequiredValueCollectionInterface;
}
