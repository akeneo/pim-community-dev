<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Model;

/**
 * Manage sources
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SourceManager
{

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->manager = $objectManager;
    }

    /**
     * Get entity repository
     *
     * @return EntityRepository
     */
    public function getEntityRepository()
    {
        return $this->manager->getRepository($this->getEntityShortname());
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getEntityClass()
    {
        return $this->manager->getClassMetadata($this->getEntityShortname())->getName();
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewEntityInstance()
    {
        $class = $this->getEntityClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityShortname()
    {
        return 'PimCatalogTaxinomyBundle:Source';
    }

}
