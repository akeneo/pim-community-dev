<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Webmozart\Assert\Assert;

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
    private string $familyCode;

    /** @var array<string, RequiredAttributesMaskForChannelAndLocale> */
    private array $masks;

    public function __construct(string $familyCode, array $masksPerChannelAndLocale)
    {
        $this->familyCode = $familyCode;
        Assert::allIsInstanceOf($masksPerChannelAndLocale, RequiredAttributesMaskForChannelAndLocale::class);
        $this->masks = [];
        foreach ($masksPerChannelAndLocale as $mask) {
            $key = \sprintf('%s-%s', $mask->localeCode(), $mask->channelCode());
            $this->masks[$key] = $mask;
        }
    }

    /**
     * @return RequiredAttributesMaskForChannelAndLocale[]
     */
    public function masks(): array
    {
        return \array_values($this->masks);
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

    public function merge(RequiredAttributesMask $otherRequiredAttributesMask): RequiredAttributesMask
    {
        $mergedMasks = $this->masks;

        foreach ($otherRequiredAttributesMask->masks() as $newMask) {
            $key = \sprintf('%s-%s', $newMask->localeCode(), $newMask->channelCode());

            if (isset($mergedMasks[$key])) {
                $mergedMasks[$key] = new RequiredAttributesMaskForChannelAndLocale(
                    $newMask->channelCode(),
                    $newMask->localeCode(),
                    \array_values(\array_unique(\array_merge($mergedMasks[$key]->mask(), $newMask->mask())))
                );
            } else {
                $mergedMasks[$key] = $newMask;
            }
        }

        return new RequiredAttributesMask($this->familyCode, \array_values($mergedMasks));
    }
}
