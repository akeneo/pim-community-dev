<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\MissingRequiredAttributes;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingRequiredValuesCalculator
{
    /** @var CachedObjectRepositoryInterface */
    private $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    private $localeRepository;

    /** @var ValueCompleteCheckerInterface */
    private $valueCompleteChecker;

    /**
     * @param ValueCompleteCheckerInterface   $valueCompleteChecker
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param CachedObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        ValueFactory $valueFactory,
        ValueCompleteCheckerInterface $valueCompleteChecker,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->valueFactory = $valueFactory;
        $this->valueCompleteChecker = $valueCompleteChecker;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    public function generate(
        FamilyInterface $family,
        ValueCollectionInterface $values
    ): ValueCollectionInterface {
        $missingRequiredValues = new ValueCollection();

        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            $channel = $attributeRequirement->getChannel();
            foreach ($channel->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $attribute = $attributeRequirement->getAttribute();
                    if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                        continue;
                    }

                    $value = $values->getByCodes(
                        $attributeRequirement->getAttribute()->getCode(),
                        $attribute->isScopable() ? $channel->getCode() : null,
                        $attribute->isLocalizable() ? $locale->getCode() : null
                    );

                    if (null === $value) {
                        $value = $this->valueFactory->create(
                            $attribute,
                            $attribute->isScopable() ? $channel->getCode() : null,
                            $attribute->isLocalizable() ? $locale->getCode() : null,
                            null
                        );

                        $missingRequiredValues->add($value);
                    }

                    if (!$this->valueCompleteChecker->isComplete($value, $channel, $locale)) {
                        $missingRequiredValues->add($value);
                    }
                }
            }
        }

        return $missingRequiredValues; // pvm1, pvm2, pvm3
    }
}

