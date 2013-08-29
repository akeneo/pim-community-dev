<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator;

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
     * @param ProductManager $productManager
     * @param EntityManager $em
     */
    public function __construct(CompletenessCalculator $calculator, ProductManager $productManager, EntityManager $em)
    {
        $this->completenessCalculator = $calculator;
        $this->productManager         = $productManager;
        $this->entityManager          = $em;

        $this->pendings = array();
    }

    /**
     * Execute calculator on each needed part defined in pending completeness
     * - channels
     * - locales
     * - families
     */
    public function execute()
    {
        $products = $this->getProductsToCalculate();
        $channels = $this->getPendingChannels();
        $this->calculate($products, $channels);

        $locales = $this->getPendingLocales();
        $this->calculate($products, array(), $locales);
        $this->saveCompletenesses($products);

        $families = $this->getPendingFamilies();
        $products = $this->getProductsToCalculate($families);
        $this->calculate($products);
        $this->saveCompletenesses($products);
    }

    /**
     * Launch calculator for specific channels, locales and products
     * Then automatically remove concerned pendings
     * @param array $products
     * @param array $channels
     * @param array $locales
     */
    protected function calculate(array $products, array $channels = array(), array $locales = array())
    {
        $this->completenessCalculator->setChannels($channels);
        $this->completenessCalculator->setLocales($locales);
        $this->completenessCalculator->calculate($products);
        $this->removePendings();
    }

    /**
     * Save products with cascading persist on completeness entities linked
     *
     * @param \Pim\Bundle\ProductBundle\Entity\Product[] $products
     */
    protected function saveCompletenesses(array $products)
    {
        foreach ($products as $product) {
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /**
     * Get products to be calculated
     *
     * @param \Pim\Bundle\ProductBundle\Entity\Family[]
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getProductsToCalculate(array $families = array())
    {
        $flexibleRepo = $this->productManager->getFlexibleRepository();
        if (!empty($families)) {
            return $flexibleRepo->findBy(array('family' => $families));
        } else {
            return $flexibleRepo->findByExistingFamily($families);
        }
    }

    /**
     * Find pending completeness and channels which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Channel[]
     */
    protected function getPendingChannels()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('channel');

        $channels = array();
        foreach ($this->pendings as $pendingChannel) {
            if (!in_array($pendingChannel->getChannel(), $channels)) {
                $channels[] = $pendingChannel->getChannel();
            }
        }

        return $channels;
    }

    /**
     * Find pending completeness and locales which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Locale[]
     */
    protected function getPendingLocales()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('locale');

        $locales = array();
        foreach ($this->pendings as $pendingLocale) {
            if (!in_array($pendingLocale->getLocale(), $locales)) {
                $locales[] = $pendingLocale->getLocale();
            }
        }

        return $locales;
    }

    /**
     * Find pending completeness and families which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Family[]
     */
    protected function getPendingFamilies()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('family');

        $families = array();
        foreach ($this->pendings as $pendingFamily) {
            if (!in_array($pendingFamily->getFamily(), $families)) {
                $families[] = $pendingFamily->getFamily();
            }
        }

        return $families;
    }

    /**
     * Get repository for pending completeness entity
     *
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
