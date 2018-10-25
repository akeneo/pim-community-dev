<?php

namespace Pim\Component\Catalog\Completeness;

use Pim\Component\Catalog\Model\ChannelInterface;
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
     * @deprecated to remove as completeness is generated on the fly when a product is saved since 2.x
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
     * @deprecated to remove as completeness is generated on the fly when a product is saved since 2.x
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
     * @deprecated to remove as it is not used
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
            ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
        ];

        $options = [
            'filters' => $filters ?? $defaultFilters
        ];

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
     *
     * The current completenesses collection is first cleared, then newly calculated ones are set to the product.
     *
     * @param ProductInterface $product
     */
    protected function calculateProductCompletenesses(ProductInterface $product)
    {
        $completenessCollection = $product->getCompletenesses();

        $newCompletenesses = $this->completenessCalculator->calculate($product);

        $this->updateExistingCompletenesses($completenessCollection, $newCompletenesses);

        $currentLocalesChannels = [];
        foreach ($completenessCollection as $currentCompleteness) {
            $currentLocalesChannels[] = $currentCompleteness->getLocale()->getId() . '/' . $currentCompleteness->getChannel()->getId();
        }

        $newLocalesChannels = [];
        foreach ($newCompletenesses as $newCompleteness) {
            $newLocalesChannels[] = $newCompleteness->getLocale()->getId() . '/' . $newCompleteness->getChannel()->getId();
        }

        $completenessesToAdd = array_diff($newLocalesChannels, $currentLocalesChannels);
        $this->addNewCompletenesses($completenessCollection, $newCompletenesses, $completenessesToAdd);

        $completenessesToRemove = array_diff($currentLocalesChannels, $newLocalesChannels);
        $this->removeOutdatedCompletenesses($completenessCollection, $completenessesToRemove);
    }

    private function updateExistingCompletenesses($completenessCollection, $newCompletenesses)
    {
        foreach ($completenessCollection as $currentCompleteness) {
            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === $currentCompleteness->getLocale()->getId()
                    && $newCompleteness->getChannel()->getId() === $currentCompleteness->getChannel()->getId()
                ) {
                    $currentCompleteness->setRatio($newCompleteness->getRatio());
                    $currentCompleteness->setMissingCount($newCompleteness->getMissingCount());
                    $currentCompleteness->setRequiredCount($newCompleteness->getRequiredCount());
                }
            }
        }
    }

    private function addNewCompletenesses($completenessCollection, $newCompletenesses, $completenessesToAdd)
    {
        foreach ($completenessesToAdd as $completenessToAdd) {
            list($localeId, $channelId) = explode('/', $completenessToAdd);

            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === (int) $localeId && $newCompleteness->getChannel()->getId() === (int) $channelId) {
                    $completenessCollection->add($newCompleteness);
                }
            }
        }
    }

    private function removeOutdatedCompletenesses($completenessCollection, $completenessesToRemove)
    {
        foreach ($completenessesToRemove as $completenessToRemove) {
            list($localeId, $channelId) = explode('/', $completenessToRemove);

            foreach ($completenessCollection as $currentCompleteness) {
                if ($currentCompleteness->getLocale()->getId() === (int) $localeId && $currentCompleteness->getChannel()->getId() === (int) $channelId) {
                    $completenessCollection->removeElement($currentCompleteness);
                }
            }
        }
    }
}
