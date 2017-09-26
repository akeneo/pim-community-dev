<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\MissingRequiredAttributes;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * Object that holds the list of missing required attributes for a channel and locale.
 *
 * The CompletenessCalculator uses it to generate the completeness for a product, channel and scope.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *            // missing required value
 */
class MissingRequiredValues
{
    private $channel;

    private $locale;

    /** @var array */
    private $missingRequiredAttributes = [];

    public function add(AttributeInterface $attribute, LocaleInterface $locale, ChannelInterface $channel)
    {
        $channelCode = $channel->getCode();
        $localeCode = $locale->getCode();
        if (!isset($this->missingRequiredAttributes[$channelCode][$localeCode])) {
            $this->missingRequiredAttributes[$channelCode][$localeCode] = new ArrayCollection();
        }

        $this->missingRequiredAttributes[$channelCode][$localeCode]->add($attribute);
    }

    public function getChannels()
    {
        return array_keys($this->missingRequiredAttributes);
    }

    public function getLocales()
    {
        $locales = [];
        foreach ($this->missingRequiredAttributes as $channel => $missingRequiredForChannel) {
            $locales = array_merge($locales, array_keys($missingRequiredForChannel));
        }

        return $locales;
    }
}
