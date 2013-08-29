<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Pim\Bundle\ProductBundle\Manager\LocaleManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator;

use Doctrine\ORM\EntityManager;

/**
 * Batch launching the calculator
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCompletenessCalculator
{
    /**
     * @var \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator $completenessCalculator
     */
    protected $completenessCalculator;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ChannelManager $channelManager
     */
    protected $channelManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\LocaleManager $localeManager
     */
    protected $localeManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ProductManager $productManager
     */
    protected $productManager;

    /**
     * @var \Doctrine\ORM\EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\PendingCompleteness[]
     */
    protected $pendings;

    /**
     * Constructor
     * @param CompletenessCalculator $calculator
     * @param ChannelManager $channelManager
     * @param LocaleManager $localeManager
     * @param ProductManager $productManager
     * @param EntityManager $em
     */
    public function __construct(
        CompletenessCalculator $calculator,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        ProductManager $productManager,
        EntityManager $em
    ) {
        $this->completenessCalculator = $calculator;
        $this->channelManager         = $channelManager;
        $this->localeManager          = $localeManager;
        $this->productManager         = $productManager;
        $this->entityManager          = $em;

        $this->pendings = array();
    }

    /**
     * TODO : Maybe we can save completenesses only one time !
     */
    public function execute()
    {
        $products = $this->getProductsToCalculate();

        $channels = $this->getPendingChannels();
        $this->completenessCalculator->setChannels($channels);
        $this->completenessCalculator->calculate($products);
        $this->saveCompletenesses($products);
//         $this->removePendings();

        $locales = $this->getPendingLocales();
        $this->completenessCalculator->setLocales($locales);
        $this->completenessCalculator->calculate($products);
        $this->saveCompletenesses($products);
//         $this->removePendings();

//         $completenesses = $this->completenessCalculator->calculate($products);

//         foreach ($products as $product) {
//             var_dump(count($product->getCompletenesses()));
//         }

    }

    /**
     *
     * @param unknown_type $products
     */
    protected function saveCompletenesses($products)
    {
        foreach ($products as $product) {
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /**
     * Get products to be calculated
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getProductsToCalculate()
    {
        return $this
            ->productManager
            ->getFlexibleRepository()
            ->findByExistingFamily();
    }

    protected function getPendingChannels()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findPendingChannels();

        $channels = array();
        foreach ($this->pendings as $pendingChannel) {
            if (!in_array($pendingChannel->getChannel(), $channels)) {
                $channels[] = $pendingChannel->getChannel();
            }
        }

        return $channels;
    }

    protected function getPendingLocales()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findPendingLocales();

        $locales = array();
        foreach ($this->pendings as $pendingLocale) {
            if (in_array($pendingLocale->getLocale(), $locales)) {
                $locales[] = $pendingLocale->getLocale();
            }
        }

        return $locales;
    }

    /**
     * Get repository for pending completeness entity
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPendingCompletenessRepository()
    {
        return $this->entityManager->getRepository('PimProductBundle:PendingCompleteness');
    }

    /**
     * Remove pendings entities from database
     */
    protected function removePendings()
    {
        foreach ($this->pendings as $pending) {
            $this->entityManager->remove($pending);
        }

        $this->entityManager->flush();
//         $this->entityManager->clear();
    }
}
