<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessQueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Manages completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManager
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var CompletenessQueryBuilder
     */
    protected $completenessQueryBuilder;

    /**
     * Constructor
     *
     * @param RegistryInterface        $doctrine
     * @param CompletenessQueryBuilder $completenessQueryBuilder
     */
    public function __construct(RegistryInterface $doctrine, CompletenessQueryBuilder $completenessQueryBuilder)
    {
        $this->doctrine = $doctrine;
        $this->completenessQueryBuilder = $completenessQueryBuilder;
    }

    /**
     * Insert missing completenesses for a given channel
     *
     * @param Channel $channel
     */
    public function createChannelCompletenesses(Channel $channel)
    {
        $this->createCompletenesses(array('channel' => $channel->getId()));
    }

    /**
     * Insert missing completenesses for a given product
     *
     * @param Product $product
     */
    public function createProductCompletenesses(Product $product)
    {
        $this->createCompletenesses(array('product' => $product->getId()));
    }

    /**
     * Insert n missing completenesses
     *
     * @param int $limit
     */
    public function createAllCompletenesses($limit = 100)
    {
        $this->createCompletenesses(array(), $limit);
    }

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product)
    {
        if ($product->getId()) {
            $query = $this->doctrine->getManager()->createQuery(
                'DELETE FROM Pim\Bundle\CatalogBundle\Entity\Completeness c WHERE c.product = :product'
            );
            $query->setParameter('product', $product);
            $query->execute();
        }
    }

    /**
     * Insert missing completeness according to the criteria
     *
     * @param array   $criteria
     * @param inreger $limit
     */
    protected function createCompletenesses(array $criteria, $limit = null)
    {
        $sql = $this->completenessQueryBuilder->getInsertCompletenessSQL($criteria, $limit);
        $stmt = $this->doctrine->getConnection()->prepare($sql);

        foreach ($criteria as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }
        $stmt->execute();
    }
}
