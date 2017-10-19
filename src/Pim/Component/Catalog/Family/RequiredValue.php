<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Family;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequiredValue
{
    private $channel;

    private $locale;

    private $attribute;

    /**
     * @param ChannelInterface   $channel
     * @param LocaleInterface    $locale
     * @param AttributeInterface $attribute
     */
    public function __construct(ChannelInterface $channel, LocaleInterface $locale, AttributeInterface $attribute)
    {
        $this->channel = $channel;
        $this->locale = $locale;
        $this->attribute = $attribute;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * @return ChannelInterface
     */
    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }
}
