<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Family\RequiredValues;
use Pim\Component\Catalog\MissingRequiredAttributes\MissingRequiredValuesCalculator;
use Pim\Component\Catalog\Model\ProductInterface;

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
    /** @var CachedObjectRepositoryInterface */
    private $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    private $localeRepository;

    /** @var MissingRequiredValuesCalculator */
    private $missingRequiredValuesCalculator;

    /** @var RequiredValues */
    private $requiredValuesGenerator;

    /** @var string */
    private $completenessClass;

    /**
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param CachedObjectRepositoryInterface $localeRepository
     * @param MissingRequiredValuesCalculator $missingRequiredAttributesCalculator
     * @param RequiredValues                  $requiredValuesGenerator
     * @param string                          $completenessClass
     */
    public function __construct(
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository,
        MissingRequiredValuesCalculator $missingRequiredAttributesCalculator,
        RequiredValues $requiredValuesGenerator,
        $completenessClass
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->missingRequiredValuesCalculator = $missingRequiredAttributesCalculator;
        $this->requiredValuesGenerator = $requiredValuesGenerator;
        $this->completenessClass = $completenessClass;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(ProductInterface $product): array
    {
        if (null === $product->getFamily()) {
            return [];
        }

        $completenesses = [];

        // Get all required product values
        $requiredValues = $this->requiredValuesGenerator->fromFamily($product->getFamily()); // pv1, pv2, pv3, pv4

        // Get all missing required product values
        $missingRequiredValues = $this->missingRequiredValuesCalculator->generate(
            $product->getFamily(),
            $product->getValues()
        );
        // pvm1, pvm2
        $processedChannelsAndLocales = [];

        foreach ($product->getFamily()->getAttributeRequirements() as $attributeRequirement) {
            $channel = $attributeRequirement->getChannel();
            foreach ($channel->getLocales() as $locale) {
                if (isset($processedChannelsAndLocales[$channel->getCode()][$locale->getCode()])) {
                    continue;
                }
                $processedChannelsAndLocales[$channel->getCode()][$locale->getCode()] = true;

                $missingRequiredAttributes = $this->getMissingRequiredAttributes(
                    $missingRequiredValues,
                    $channel,
                    $locale
                );

                $requiredValuesCount = $this->getRequiredValuesCount($requiredValues, $channel, $locale);

                $completenesses[] = new $this->completenessClass(
                    $product,
                    $channel,
                    $locale,
                    new ArrayCollection($missingRequiredAttributes),
                    count($missingRequiredAttributes),
                    $requiredValuesCount
                );
            }
        }

        return $completenesses;
    }

    private function getMissingRequiredAttributes($missingRequiredValues, $channel, $locale)
    {
        $missingRequiredAttributes = [];

        $channelCode = $channel->getCode();
        $localeCode = $locale->getCode();

        foreach ($missingRequiredValues as $missingRequiredValue) {
            if (($channelCode === $missingRequiredValue->getScope() && $localeCode === $missingRequiredValue->getLocale()) ||
                ($channelCode === $missingRequiredValue->getScope() && null === $missingRequiredValue->getLocale()) ||
                (null === $missingRequiredValue->getScope() && $localeCode === $missingRequiredValue->getLocale()) ||
                (null === $missingRequiredValue->getScope() && null === $missingRequiredValue->getLocale())
            ) {
                $missingRequiredAttributes[] = $missingRequiredValue->getAttribute();
            }
        }

        return $missingRequiredAttributes;
    }

    private function getRequiredValuesCount($requiredValues, $channel, $locale)
    {
        $requiredValuesCount = 0;
        $channelCode = $channel->getCode();
        $localeCode = $locale->getCode();

        foreach ($requiredValues as $requiredValue) {
            if (($channelCode === $requiredValue->getChannel()->getCode() && $localeCode === $requiredValue->getLocale()->getCode()) ||
                ($channelCode === $requiredValue->getChannel() && null === $requiredValue->getLocale()) ||
                (null === $requiredValue->getChannel() && $localeCode === $requiredValue->getLocale()) ||
                (null === $requiredValue->getChannel() && null === $requiredValue->getLocale())
            ) {
                $requiredValuesCount++;
            }
        }

        return $requiredValuesCount;
    }
}
