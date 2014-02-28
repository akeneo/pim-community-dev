<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * Generate the completeness when Product are in ORM
 * storage
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGenerator implements CompletenessGeneratorInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;
    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $productValueClass;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $productClass
     * @param string            $productValueClass
     */
    public function __construct(ManagerRegistry $doctrine, $productClass, $productValueClass)
    {
        $this->doctrine = $doctrine;
        $this->productClass = $productClass;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $criteria = array(), $limit = null)
    {
        // @TODO Not implemented yet
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(ProductInterface $product)
    {
        // @TODO Not implemented yet
        return;
    }


}
