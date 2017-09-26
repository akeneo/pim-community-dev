<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Family;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;

/**
 *
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequiredValues
{
    /** @var ValueFactory */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param FamilyInterface $family
     *
     * @return RequiredValuesCollection[]
     */
    public function fromFamily(FamilyInterface $family): ArrayCollection
    {
        $requiredValues = new ArrayCollection();

        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            foreach ($attributeRequirement->getChannel()->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $channel = $attributeRequirement->getChannel();

                    $attribute = $attributeRequirement->getAttribute();
                    if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                        continue;
                    }

                    $requiredValue = new RequiredValue($channel, $locale, $attribute);
                    $requiredValues->add($requiredValue);
                }
            }
        }

        return $requiredValues;
    }
}
