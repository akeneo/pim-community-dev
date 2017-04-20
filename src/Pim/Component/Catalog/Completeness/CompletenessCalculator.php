<?php

namespace Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;

/**
 * Calculates the completenesses for a provided product.
 *
 * This calculator creates a "fake" collection of required product values
 * according to the product family requirements. Then, it compares this
 * collection of fake values with the real values of the product, and generates
 * a list of completenesses, one completeness for each channel/locale possible
 * combinations.
 *
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

    /** @var string */
    protected $completenessClass;

    /**
     * @param ProductValueFactory                  $productValueFactory
     * @param CachedObjectRepositoryInterface      $channelRepository
     * @param CachedObjectRepositoryInterface      $localeRepository
     * @param ProductValueCompleteCheckerInterface $productValueCompleteChecker
     * @param string                               $completenessClass
     */
    public function __construct(
        ProductValueFactory $productValueFactory,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository,
        ProductValueCompleteCheckerInterface $productValueCompleteChecker,
        $completenessClass
    ) {
        $this->productValueFactory = $productValueFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->productValueCompleteChecker = $productValueCompleteChecker;
        $this->completenessClass = $completenessClass;
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
        $requiredProductValues = $this->getRequiredProductValues($product->getFamily());

        foreach ($requiredProductValues as $channelCode => $requiredProductValuesByChannel) {
            foreach ($requiredProductValuesByChannel as $localeCode => $requiredProductValuesByChannelAndLocale) {
                $completenesses[] = $this->generateCompleteness(
                    $product,
                    $requiredProductValuesByChannelAndLocale,
                    $channelCode,
                    $localeCode
                );
            }
        }

        return $completenesses;
    }

    /**
     * Generates a two dimensional array indexed by channel and locale containing
     * the required product values for those channel/locale combinations. Those
     * are determined from the attribute requirements of the product family and
     * from the channel activated locales.
     *
     * This method takes into account the localizable and scopable characteristic
     * of the product value and local specific characteristic of the attribute.
     *
     * For example, you have 2 channels "mobile" and "print", two locales "en_US"
     * and "fr_FR", and the following attrbutes:
     * - "name" is non scopable and not localisable,
     * - "short_description" is scopable,
     * - "long_description" is scobable and localisable.
     *
     * The resulting array of product values would be like:
     * [
     *     "mobile" => [
     *         "en_US" => [
     *             ProductValueCollection {
     *                 name product value,
     *                 short_description-mobile product value,
     *                 long_description-mobile-en_US product value,
     *             }
     *         ],
     *         "fr_FR" => [
     *             ProductValueCollection {
     *                 name product value,
     *                 short_description-mobile product value,
     *                 long_description-mobile-fr_FR product value,
     *             }
     *         ],
     *     ],
     *     "print"  => [
     *         "en_US" => [
     *             ProductValueCollection {
     *                 name product value,
     *                 short_description-print product value,
     *                 long_description-print-en_US product value,
     *             }
     *         ],
     *         "fr_FR" => [
     *             ProductValueCollection {
     *                 name product value,
     *                 short_description-print product value,
     *                 long_description-print-fr_FR product value,
     *             }
     *         ],
     *     ],
     * ]
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function getRequiredProductValues(FamilyInterface $family)
    {
        $productValues = [];

        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            foreach ($attributeRequirement->getChannel()->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $channelCode = $attributeRequirement->getChannelCode();
                    $localeCode = $locale->getCode();

                    $attribute = $attributeRequirement->getAttribute();
                    if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                        continue;
                    }

                    $productValue = $this->productValueFactory->create(
                        $attribute,
                        $attribute->isScopable() ? $channelCode : null,
                        $attribute->isLocalizable() ? $localeCode : null,
                        null
                    );

                    if (!isset($productValues[$channelCode][$localeCode])) {
                        $productValues[$channelCode][$localeCode] = new ProductValueCollection();
                    }
                    $productValues[$channelCode][$localeCode]->add($productValue);
                }
            }
        }

        return $productValues;
    }

    /**
     * Generates one completeness for the given required product value, channel
     * code, locale code and the product values to compare.
     *
     * @param ProductInterface                $product
     * @param ProductValueCollectionInterface $requiredValues
     * @param string                          $channelCode
     * @param string                          $localeCode
     *
     * @return CompletenessInterface
     */
    protected function generateCompleteness(
        ProductInterface $product,
        ProductValueCollectionInterface $requiredValues,
        $channelCode,
        $localeCode
    ) {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        $actualValues = $product->getValues();
        $missingAttributes = new ArrayCollection();
        $missingCount = 0;
        $requiredCount = 0;

        foreach ($requiredValues as $requiredValue) {
            $attribute = $requiredValue->getAttribute();

            $productValue = $actualValues->getByCodes(
                $attribute->getCode(),
                $requiredValue->getScope(),
                $requiredValue->getLocale()
            );

            if (null === $productValue ||
                !$this->productValueCompleteChecker->isComplete($productValue, $channel, $locale)
            ) {
                if (!$missingAttributes->contains($attribute)) {
                    $missingAttributes->add($attribute);
                    $missingCount++;
                }
            }

            $requiredCount++;
        }

        $completeness = $this->createCompleteness(
            $product,
            $channel,
            $locale,
            $missingAttributes,
            $missingCount,
            $requiredCount
        );

        return $completeness;
    }

    /**
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param Collection       $missingAttributes
     * @param int              $missingCount
     * @param int              $requiredCount
     *
     * @return CompletenessInterface
     */
    private function createCompleteness(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale,
        Collection $missingAttributes,
        $missingCount,
        $requiredCount
    ) {
        return new $this->completenessClass(
            $product,
            $channel,
            $locale,
            $missingAttributes,
            $missingCount,
            $requiredCount
        );
    }
}
