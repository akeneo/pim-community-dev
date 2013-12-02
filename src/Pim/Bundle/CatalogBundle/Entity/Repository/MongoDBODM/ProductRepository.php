<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository
{
    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;


    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Set flexible entity config
     *
     * @param array $config
     *
     * @return FlexibleEntityRepository
     */
    public function setFlexibleConfig($config)                                  
    {
        $this->flexibleConfig = $config;

        return $this;
    }
}
