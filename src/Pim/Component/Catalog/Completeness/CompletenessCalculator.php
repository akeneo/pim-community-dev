<?php

namespace Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessCalculator implements CompletenessCalculatorInterface
{
    /** @var ProductValueFactory */
    protected $productValueFactory;

    /** @var CachedObjectRepositoryInterface */
    protected $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $localeRepository;

    /** @var ProductValueCompleteCheckerInterface */
    protected $productValueCompleteChecker;

    /**
     * @param ProductValueFactory                  $productValueFactory
     * @param CachedObjectRepositoryInterface      $channelRepository
     * @param CachedObjectRepositoryInterface      $localeRepository
     * @param ProductValueCompleteCheckerInterface $productValueCompleteChecker
     */
    public function __construct(
        ProductValueFactory $productValueFactory,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository,
        ProductValueCompleteCheckerInterface $productValueCompleteChecker
    ) {
        $this->productValueFactory = $productValueFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->productValueCompleteChecker = $productValueCompleteChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(ProductInterface $product)
    {
        if (null === $product->getFamily()) {
            return [];
        }

        $completenesses = [];
        $requiredProductValueCollectionsList = $this->getRequiredProductValueCollections($product->getFamily());
        $actualValues = $product->getValues();

        foreach ($requiredProductValueCollectionsList as $channelCode => $requiredProductValueCollections) {
            foreach ($requiredProductValueCollections as $localeCode => $requiredProductValueCollection) {
                $completenesses[$channelCode][$localeCode] = $this->generateCompleteness(
                    $product,
                    $requiredProductValueCollection,
                    $actualValues,
                    $channelCode,
                    $localeCode
                );
            }
        }

        return $completenesses;
    }

    /**
     * Generates a two dimensional array indexed by scope and locale containing the required product value collections.
     *
     * This method takes into account the localizable and scopable characteristic of the product value (meaning a
     * product value can be added to multiple productValueCollection if not localizable for instance).
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function getRequiredProductValueCollections(FamilyInterface $family)
    {
        $productValueCollections = [];

        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            foreach ($attributeRequirement->getChannel()->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $channelCode = $attributeRequirement->getChannelCode();
                    $localeCode = $locale->getCode();

                    $attribute = $attributeRequirement->getAttribute();

                    if ($attribute->isLocaleSpecific() && !in_array($locale, $attribute->getAvailableLocaleCodes())) {
                        continue;
                    }

                    $value = $this->productValueFactory->create(
                        $attribute,
                        $attribute->isScopable()? $channelCode : null,
                        $attribute->isLocalizable() ? $localeCode : null,
                        null
                    );

                    if (!isset($productValueCollections[$channelCode][$localeCode])) {
                        $productValueCollections[$channelCode][$localeCode] = new ProductValueCollection();
                    }

                    $this->addValueToCollections($value, $productValueCollections);
                }
            }
        }

        return $productValueCollections;
    }

    /**
     * Add the (empty) required product value to the right product value collections depending on the localizable and
     * scopable characteristics of product value.
     *
     * @param ProductValueInterface               $value
     * @param ProductValueCollectionInterface[][] $collectionOfProductValueCollections
     */
    protected function addValueToCollections(ProductValueInterface $value, array $collectionOfProductValueCollections)
    {
        foreach ($collectionOfProductValueCollections as $channelCode => $productValueCollections) {
            foreach ($productValueCollections as $localeCode => $productValueCollection) {
                if ((null === $value->getScope() || null !== $channelCode = $value->getScope()) &&
                    (null === $value->getLocale() || null !== $localeCode = $value->getLocale())
                ) {
                    $productValueCollection->add($value);
                }
            }
        }
    }

    /**
     * Generate one completeness for given requiredProductValue, channelcode, localeCode and the product values to
     * compare.
     *
     * @param ProductInterface                $product
     * @param ProductValueCollectionInterface $requiredProductValueCollection
     * @param ProductValueCollectionInterface $actualValues
     * @param string                          $channelCode
     * @param string                          $localeCode
     *
     * @return CompletenessInterface
     */
    protected function generateCompleteness(
        ProductInterface $product,
        ProductValueCollectionInterface $requiredProductValueCollection,
        ProductValueCollectionInterface $actualValues,
        $channelCode,
        $localeCode
    ) {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        $missingAttributes = new ArrayCollection();
        $missingCount = 0;
        $requiredCount = 0;

        foreach ($requiredProductValueCollection as $requiredProductValue) {
            $productValue = $actualValues->getByCodes(
                $requiredProductValue->getAttribute()->getCode(),
                $requiredProductValue->getScope(),
                $requiredProductValue->getLocale()
            );

            if (null === $productValue ||
                !$this->productValueCompleteChecker->isComplete($productValue, $channel, $locale)
            ) {
                $attribute = $requiredProductValue->getAttribute();

                if (!$missingAttributes->contains($attribute)) {
                    $missingAttributes->add($attribute);
                    $missingCount++;
                }
            }

            $requiredCount++;
        }

        $completeness = new Completeness(
            $product,
            $channel,
            $locale,
            $missingAttributes,
            $missingCount,
            $requiredCount
        );

        return $completeness;
    }
}
