<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * A required value is the translation of the attribute requirements
 * {@see Pim\Component\Catalog\Model\AttributeRequirementInterface}.
 *
 * Therefore a required value contains no data. It simply expresses the fact that for instance:
 *  - a "sku" is required on all channels and all locales
 *  - a description is required on the channel "ecommerce" and the locale "en_US"
 *  - a name is required on all channels and the locale "en_US"
 *  - a price is required on the channel "ecommerce" and all locales
 *  - ...
 *
 * This object gives you the information of the requirement in the matrix: attribute, channel, locale.
 * that's why the functions forAttribute, forScope and ForLocale always return an object.
 *
 * It also helps you how to retrieve the corresponding value in a value collection.
 * For instance, if the attribute is not scopable and not localizable. if we try to compute the completeness on the
 * channel 'ecommerce' and locale 'fr_FR' for this attribute.
 * - forScope will return the channel ecommerde
 * - forLocale will return the locale fr_FR
 * the information of the matrix, and
 * - scope() will return null
 * - locale('fr_FR') will return null
 *
 * This way, we decouple what is the required value in the matrix, from the way we retrieve it in the value collection
 * (depending on the attribute's settings).
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RequiredValue
{
    /** @var AttributeInterface */
    private $attribute;

    /** @var ChannelInterface */
    private $channel;

    /** @var LocaleInterface */
    private $locale;

    /**
     * @param AttributeInterface $attribute
     * @param ChannelInterface   $channel
     * @param LocaleInterface    $locale
     */
    public function __construct(
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->attribute = $attribute;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    /**
     * @return AttributeInterface
     */
    public function forAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @return ChannelInterface
     */
    public function forScope(): ChannelInterface
    {
        return $this->channel;
    }

    /**
     * @return LocaleInterface
     */
    public function forLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function attribute(): string
    {
        return $this->attribute->getCode();
    }

    /**
     * @return null|string
     */
    public function scope(): ?string
    {
        return $this->attribute->isScopable() ? $this->channel->getCode() : null;
    }

    /**
     * @param LocaleInterface $locale
     *
     * @return null|string
     */
    public function locale(): ?string
    {
        return $this->attribute->isLocalizable() ? $this->locale->getCode() : null;
    }
}
