<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsSaver;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SavedSubjectsEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Applies a rule on products
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    public function __construct(
        protected ProductsUpdater $productsUpdater,
        protected ProductsValidator $productsValidator,
        protected ProductsSaver $productsSaver,
        protected EventDispatcherInterface $eventDispatcher,
        protected EntityManagerClearerInterface $cacheClearer,
        protected FilterInterface $productFilter,
        protected NormalizerInterface $productNormalizer,
        protected $pageSize = 1000,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(new SelectedRuleEvent($rule, $subjectSet), RuleEvents::PRE_APPLY);

        $productsPage = [];
        foreach ($subjectSet->getSubjectsCursor() as $product) {
            $productsPage[] = $product;
            if (count($productsPage) >= $this->pageSize) {
                $this->updateProducts($rule, $productsPage);
                $productsPage = [];
            }
        }

        if (count($productsPage) > 0) {
            $this->updateProducts($rule, $productsPage);
        }

        $this->eventDispatcher->dispatch(new SelectedRuleEvent($rule, $subjectSet), RuleEvents::POST_APPLY);
    }

    protected function clearCache()
    {
        $this->cacheClearer->clear();
    }

    /**
     * @param ProductInterface $product
     * @param array            $filteredItem
     *
     * @return array
     */
    protected function filterIdenticalData(ProductInterface $product, array $filteredItem): array
    {
        return $this->productFilter->filter($product, $filteredItem);
    }

    /**
     * @param RuleInterface $rule
     * @param array $products
     */
    protected function updateProducts(RuleInterface $rule, array $products)
    {
        $updatedProducts = $this->productsUpdater->update($rule, $products);
        var_dump('xouxou');

        $validProducts = $this->productsValidator->validate($rule, $updatedProducts);
        $this->eventDispatcher->dispatch(new SavedSubjectsEvent($rule, $validProducts), RuleEvents::PRE_SAVE_SUBJECTS);
        $this->productsSaver->save($rule, $validProducts);
        $this->eventDispatcher->dispatch(new SavedSubjectsEvent($rule, $validProducts), RuleEvents::POST_SAVE_SUBJECTS);
        $this->clearCache();
    }
}
