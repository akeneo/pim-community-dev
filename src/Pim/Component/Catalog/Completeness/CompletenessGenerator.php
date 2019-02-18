<?php

namespace Pim\Component\Catalog\Completeness;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Simple object version of the completeness generator.
 *
 * In this implementation, methods that generate missing completenesses do NOT save the products.
 * Complenesses are only added to the products in memory. The save of the products (and of the compltenesses)
 * should be handled by the a Akeneo\Component\StorageUtils\Saver\SaverInterface service.
 *
 * @author    Julien Janvier (j.janvier@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var CompletenessCalculatorInterface */
    protected $completenessCalculator;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CompletenessCalculatorInterface     $completenessCalculator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessCalculatorInterface $completenessCalculator
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->completenessCalculator = $completenessCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissingForProduct(ProductInterface $product)
    {
        $this->calculateProductCompletenesses($product);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated as completeness is generated on the fly when a product is saved since 2.x
     *             Will be removed in 3.0.
     */
    public function generateMissingForProducts(ChannelInterface $channel, array $filters)
    {
        $products = $this->createProductQueryBuilderForMissings($channel, null, $filters)->execute();
        foreach ($products as $product) {
            $this->calculateProductCompletenesses($product);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated as completeness is generated on the fly when a product is saved since 2.x
     *             Will be removed in 3.0.
     */
    public function generateMissingForChannel(ChannelInterface $channel)
    {
        $products = $this->createProductQueryBuilderForMissings($channel)->execute();
        foreach ($products as $product) {
            $this->calculateProductCompletenesses($product);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated as completeness is generated on the fly when a product is saved since 2.x
     *             Will be removed in 3.0.
     */
    public function generateMissing()
    {
        $products = $this->createProductQueryBuilderForMissings()->execute();
        foreach ($products as $product) {
            $this->calculateProductCompletenesses($product);
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param array filters
     *
     * @return ProductQueryBuilderInterface
     */
    protected function createProductQueryBuilderForMissings(
        ChannelInterface $channel = null,
        LocaleInterface $locale = null,
        ?array $filters = null
    ) {
        $defaultFilters = [
            ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
            ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null],
        ];

        $options = ['filters' => $filters ?? $defaultFilters];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }
        if (null !== $locale) {
            $options['default_locale'] = $locale->getCode();
        }

        return $this->pqbFactory->create($options);
    }

    /**
     * Calculates current product completenesses.
     * Completenesses are updated for the existing ones, others are added/removed.
     *
     * @param ProductInterface $product
     */
    protected function calculateProductCompletenesses(ProductInterface $product)
    {
        $completenessCollection = $product->getCompletenesses();

        $newCompletenesses = $this->completenessCalculator->calculate($product);

        $this->updateExistingCompletenesses($completenessCollection, $newCompletenesses);

        $completenessLocaleAndChannelCodes = [];
        foreach ($completenessCollection as $updatedCompleteness) {
            $completenessLocaleAndChannelCodes[] =
                $updatedCompleteness->getLocale()->getId().'/'.$updatedCompleteness->getChannel()->getId();
        }

        $newLocalesChannels = [];
        foreach ($newCompletenesses as $newCompleteness) {
            $newLocalesChannels[] =
                $newCompleteness->getLocale()->getId().'/'.$newCompleteness->getChannel()->getId();
        }

        $localeAndChannelCodesOfCompletenessesToAdd = array_diff(
            $newLocalesChannels,
            $completenessLocaleAndChannelCodes
        );
        $this->addNewCompletenesses(
            $completenessCollection,
            $newCompletenesses,
            $localeAndChannelCodesOfCompletenessesToAdd
        );

        $localeAndChannelCodesOfCompletenessesToRemove = array_diff(
            $completenessLocaleAndChannelCodes,
            $newLocalesChannels
        );
        $this->removeOutdatedCompletenesses($completenessCollection, $localeAndChannelCodesOfCompletenessesToRemove);
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $newCompletenesses
     */
    private function updateExistingCompletenesses(Collection $completenessCollection, array $newCompletenesses)
    {
        foreach ($completenessCollection as $currentCompleteness) {
            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === $currentCompleteness->getLocale()->getId() &&
                    $newCompleteness->getChannel()->getId() === $currentCompleteness->getChannel()->getId()
                ) {
                    $currentCompleteness->setRatio($newCompleteness->getRatio());
                    $currentCompleteness->setMissingCount($newCompleteness->getMissingCount());
                    $currentCompleteness->setRequiredCount($newCompleteness->getRequiredCount());
                    $this->updateMissingAttributes(
                        $currentCompleteness->getMissingAttributes(),
                        $newCompleteness->getMissingAttributes()
                    );
                }
            }
        }
    }

    /**
     * @param Collection $currentMissingAttributes
     * @param Collection $newMissingAttributes
     */
    private function updateMissingAttributes(
        Collection $currentMissingAttributes,
        Collection $newMissingAttributes
    ): void {
        foreach ($currentMissingAttributes as $currentMissingAttribute) {
            if (!$newMissingAttributes->contains($currentMissingAttribute)) {
                $currentMissingAttributes->removeElement($currentMissingAttribute);
            }
        }
        foreach ($newMissingAttributes as $newMissingAttribute) {
            if (!$currentMissingAttributes->contains($newMissingAttribute)) {
                $currentMissingAttributes->add($newMissingAttribute);
            }
        }
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $newCompletenesses
     * @param string[]                $localeAndChannelCodesOfCompletenessesToAdd
     */
    private function addNewCompletenesses(
        Collection $completenessCollection,
        array $newCompletenesses,
        array $localeAndChannelCodesOfCompletenessesToAdd
    ) {
        foreach ($localeAndChannelCodesOfCompletenessesToAdd as $completenessLocaleAndChannel) {
            [$localeCode, $channelCode] = explode('/', $completenessLocaleAndChannel);

            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === (int) $localeCode
                    && $newCompleteness->getChannel()->getId() === (int) $channelCode
                ) {
                    $completenessCollection->add($newCompleteness);
                }
            }
        }
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $localeAndChannelCodesOfCompletenessesToRemove
     */
    private function removeOutdatedCompletenesses(
        Collection $completenessCollection,
        array $localeAndChannelCodesOfCompletenessesToRemove
    ) {
        foreach ($localeAndChannelCodesOfCompletenessesToRemove as $completenessLocaleAndChannel) {
            [$localeCode, $channelCode] = explode('/', $completenessLocaleAndChannel);

            foreach ($completenessCollection as $currentCompleteness) {
                if ($currentCompleteness->getLocale()->getId() === (int) $localeCode
                    && $currentCompleteness->getChannel()->getId() === (int) $channelCode
                ) {
                    $completenessCollection->removeElement($currentCompleteness);
                }
            }
        }
    }
}
