<?php

namespace Pim\Component\Catalog\Completeness;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Calculates the completenesses for a provided product.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessCalculator implements CompletenessCalculatorInterface
{
    /** @var RequiredValueCollectionFactory */
    private $requiredValueCollectionFactory;

    /** @var IncompleteValueCollectionFactory */
    private $incompleteValueCollectionFactory;

    /** @var string */
    private $completenessClass;

    /**
     * @param RequiredValueCollectionFactory   $requiredValueCollectionFactory
     * @param IncompleteValueCollectionFactory $incompleteValueCollectionFactory
     * @param string                           $completenessClass
     */
    public function __construct(
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory,
        $completenessClass
    ) {
        $this->completenessClass = $completenessClass;
        $this->requiredValueCollectionFactory = $requiredValueCollectionFactory;
        $this->incompleteValueCollectionFactory = $incompleteValueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(ProductInterface $product)
    {
        $family = $product->getFamily();
        if (null === $family) {
            return [];
        }

        $completenesses = [];

        foreach ($this->getChannelsFromRequirements($family) as $channel) {
            $requiredValuesForChannel = $this->requiredValueCollectionFactory->forChannel($family, $channel);

            foreach ($channel->getLocales() as $locale) {
                $requiredValues = $requiredValuesForChannel->filterByChannelAndLocale($channel, $locale);
                $incompleteValues = $this->incompleteValueCollectionFactory->forChannelAndLocale(
                    $requiredValues,
                    $channel,
                    $locale,
                    $product
                );

                $completenesses[] = $this->createCompleteness(
                    $product,
                    $channel,
                    $locale,
                    $incompleteValues->attributes(),
                    $incompleteValues->count(),
                    $requiredValues->count()
                );
            }
        }

        return $completenesses;
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

    /**
     * @param FamilyInterface $family
     *
     * @return Collection
     */
    private function getChannelsFromRequirements(FamilyInterface $family): Collection
    {
        $channels = new ArrayCollection();
        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            $channel = $attributeRequirement->getChannel();
            if (!$channels->contains($channel)) {
                $channels->add($channel);
            }
        }

        return $channels;
    }
}
