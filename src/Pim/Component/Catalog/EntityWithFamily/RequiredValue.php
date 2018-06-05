<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * A required value is the translation of the attribute requirements
 * {@see Pim\Component\Catalog\Model\AttributeRequirementInterface} in terms of values.
 *
 * Therefore a required value contains no data. It simply expresses the fact that for instance:
 *  - a "sku" is required on all channels and all locales
 *  - a description is required on the channel "ecommerce" and the locale "en_US"
 *  - a name is required on all channels and the locale "en_US"
 *  - a price is required on the channel "ecommerce" and all locales
 *  - ...
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

    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    public function getScope(): ChannelInterface
    {
        return $this->channel;
    }

    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function forAttributeCode(): string
    {
        return $this->attribute->getCode();
    }

    /**
     * @return null|string
     */
    public function forScope(): ?string
    {
        return $this->attribute->isScopable() ? $this->channel->getCode() : null;
    }

    /**
     * @return null|string
     */
    public function forLocale(LocaleInterface $locale): ?string
    {
        if (!$this->attribute->isLocalizable() &&
            $this->attribute->isLocaleSpecific() &&
            $this->attribute->hasLocaleSpecific($locale)
        ) {
            return null;
        }

        return $this->locale->getCode();
    }
}
