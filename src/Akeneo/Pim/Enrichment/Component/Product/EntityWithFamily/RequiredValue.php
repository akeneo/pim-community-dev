<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * A required value is the translation of the attribute requirements
 * {@see Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface}.
 *
 * Therefore a required value contains no data. It simply expresses the fact that for instance:
 *  - a "sku" is required on all channels and all locales
 *  - a description is required on the channel "ecommerce" and the locale "en_US"
 *  - a name is required on all channels and the locale "en_US"
 *  - a price is required on the channel "ecommerce" and all locales
 *  - ...
 *
 * This object gives you the information of the requirement in the matrix: attribute, channel, locale.
 * that's why the functions forAttribute, forChannel and ForLocale always return an object representing the value in those
 * axis.
 *
 * It also helps to retrieve the corresponding value in a value collection:
 *
 * For instance, if the attribute 'description' is not scopable and not localizable and we try to compute the completeness on the
 * channel 'ecommerce' and locale 'fr_FR' for this attribute.
 *
 * - forAttribute() will return the attribute description object
 * - forChannel() will return the channel ecommerce object
 * - forLocale() will return the locale fr_FR object
 *
 * However, to retrieve the corresponding value in the value collection, we will use:
 * - attribute() will return the code of the description attribute 'description'
 * - channel() will return null (because the attribute is note scopable)
 * - locale() will return null (because the attribute is not localizable)
 *
 * This way, the required value holds two kind of information:
 * - The attribute, channel, locale for which this required value is relevant (using the for* functions)
 * - the way we retrieve it in the value collection (depending on the attribute's settings).
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RequiredValue
{
    /** @var AttributeInterface */
    private $forAttribute;

    /** @var ChannelInterface */
    private $forChannel;

    /** @var LocaleInterface */
    private $forLocale;

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
        $this->forAttribute = $attribute;
        $this->forChannel = $channel;
        $this->forLocale = $locale;
    }

    /**
     * @return AttributeInterface
     */
    public function forAttribute(): AttributeInterface
    {
        return $this->forAttribute;
    }

    /**
     * @return ChannelInterface
     */
    public function forChannel(): ChannelInterface
    {
        return $this->forChannel;
    }

    /**
     * @return LocaleInterface
     */
    public function forLocale(): LocaleInterface
    {
        return $this->forLocale;
    }

    /**
     * @return string
     */
    public function attribute(): string
    {
        return $this->forAttribute->getCode();
    }

    /**
     * @return null|string
     */
    public function channel(): ?string
    {
        return $this->forAttribute->isScopable() ? $this->forChannel->getCode() : null;
    }

    /**
     * @return null|string
     */
    public function locale(): ?string
    {
        return $this->forAttribute->isLocalizable() ? $this->forLocale->getCode() : null;
    }
}
