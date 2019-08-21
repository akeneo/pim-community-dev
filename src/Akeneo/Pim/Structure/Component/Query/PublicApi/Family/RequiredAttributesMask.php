<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

/**
 * This mask is done to gather all the masks for a given family
 * e.g:
 *     Given a channel "ecommerce" with locales "en_US", "fr_FR"
 *     AND a channel "tablet" with locales "en_UK"
 *     AND a family "t-shirts" with attributes "size"
 *     THEN there are 3 masks for this family
 *             - size-ecommerce-en_US
 *             - size-ecommerce-fr_FR
 *             - size-tablet-en_UK
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequiredAttributesMask
{
    /** @var string */
    private $familyCode;

    /** @var RequiredAttributesMaskForChannelAndLocale[] */
    private $masks;

    public function __construct(string $familyCode, array $masksPerChannelAndLocale)
    {
        $this->familyCode = $familyCode;
        $this->masks = $masksPerChannelAndLocale;
    }

    /**
     * @return RequiredAttributesMaskForChannelAndLocale[]
     */
    public function masks(): array
    {
        return $this->masks;
    }

    public function requiredAttributesMaskForChannelAndLocale(string $channelCode, string $localeCode): RequiredAttributesMaskForChannelAndLocale
    {
        foreach ($this->masks as $requiredAttributesMaskPerChannelAndLocale) {
            if ($channelCode === $requiredAttributesMaskPerChannelAndLocale->channelCode() && $localeCode === $requiredAttributesMaskPerChannelAndLocale->localeCode()) {
                return $requiredAttributesMaskPerChannelAndLocale;
            }
        }

        throw new \InvalidArgumentException(
            sprintf("The completeness family mask for family %s, channel %s and locale %s does not exist", $this->familyCode, $channelCode, $localeCode)
        );
    }
}
