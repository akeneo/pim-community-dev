<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * Simple factory of a "required value" collection.
 *
 * @internal
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RequiredValueCollectionFactory
{
    /**
     * Create a collection of required values from family's attribute requirements for the given $channel.
     *
     * @param FamilyInterface  $family
     * @param ChannelInterface $channel
     *
     * @return RequiredValueCollection
     */
    public function forChannel(FamilyInterface $family, ChannelInterface $channel): RequiredValueCollection
    {
        $requiredValues = [];

        foreach ($this->filterRequirementsByChannel($family, $channel) as $attributeRequirement) {
            foreach ($attributeRequirement->getChannel()->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $channel = $attributeRequirement->getChannel();

                    $attribute = $attributeRequirement->getAttribute();
                    $channelCode = $attribute->isScopable() ? $channel->getCode() : null;
                    $localeCode = $attribute->isLocalizable() ? $locale->getCode() : null;
                    $getter = $this->simpleValueGetter();

                    if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                        continue;
                    }

                    if (!$attribute->isLocalizable() && $attribute->isLocaleSpecific() && $attribute->hasLocaleSpecific($locale)) {
                        $localeCode = $locale->getCode();
                        $getter = $this->localeSpecficGetter();
                    }

                    $requiredValues[] = new RequiredValue($attribute, $channelCode, $localeCode, $getter);
                }
            }
        }

        return new RequiredValueCollection($requiredValues);
    }

    /**
     * @param FamilyInterface  $family
     * @param ChannelInterface $channel
     *
     * @return Collection
     */
    private function filterRequirementsByChannel(FamilyInterface $family, ChannelInterface $channel)
    {
        $requirements = new ArrayCollection();
        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            if ($attributeRequirement->getChannel()->getCode() === $channel->getCode() &&
                !$requirements->contains($attributeRequirement)
            ) {
                $requirements->add($attributeRequirement);
            }
        }

        return $requirements;
    }

    protected function simpleValueGetter(): \Closure
    {
        return function (
            EntityWithValuesInterface $entityWithValues,
            AttributeInterface $attribute,
            $channelCode,
            $localeCode
        ) {
            $requiredValue = new RequiredValue($attribute, $channelCode, $localeCode);

            return $entityWithValues->getValues()->getSame($requiredValue);
        };
    }

    protected function localeSpecficGetter(): \Closure
    {
        return function (
            EntityWithValuesInterface $entityWithValues,
            AttributeInterface $attribute,
            $channelCode,
            $localeCode
        ) {
            $requiredValue = new RequiredValue($attribute, $channelCode, null);

            return $entityWithValues->getValues()->getSame($requiredValue);
        };
    }
}
