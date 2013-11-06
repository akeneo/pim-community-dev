<?php

namespace Pim\Bundle\CatalogBundle\Calculator;

use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Completeness;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Product;

/**
 * Product completeness calculator
 *
 * The calculation algorithm get the required attributes for each channel
 * and validate if the value of each required attributes is not blank
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator
{
    /** @var EntityManager */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product)
    {
        $query = $this->em->createQuery(
            'DELETE FROM Pim\Bundle\CatalogBundle\Entity\Completeness c WHERE c.product = :product'
        );
        $query->setParameter('product', $product);
        $query->execute();
    }

    /**
     * Calculate missing completenesses for a given channel
     *
     * @param Channel $channel
     *
     */
    public function calculateChannelCompleteness(Channel $channel)
    {
        $this
            ->getCompletenessRepository()
            ->createChannelCompletenesses($channel);
    }


    /**
     * Calculate missing completenesses for a given product
     *
     * @param Product $product
     *
     */
    public function calculateProductCompleteness(Product $product)
    {
        $this
            ->getCompletenessRepository()
            ->createProductCompletenesses($product);
    }

    /**
     * Get the completeness repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getCompletenessRepository()
    {
        return $this->em->getRepository('PimCatalogBundle:Completeness');
    }
}
