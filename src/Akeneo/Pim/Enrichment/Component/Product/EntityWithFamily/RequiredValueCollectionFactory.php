<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
                    $attribute = $attributeRequirement->getAttribute();

                    if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                        continue;
                    }

                    $requiredValues[] = new RequiredValue($attribute, $attributeRequirement->getChannel(), $locale);
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
}
