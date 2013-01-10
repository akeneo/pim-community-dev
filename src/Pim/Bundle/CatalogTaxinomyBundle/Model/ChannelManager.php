<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Model;


/**
 * Manage channels
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelManager
{

    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get entity repository
     *
     * @return EntityRepository
     */
    public function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->getEntityShortname());
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityShortname()
    {
        return 'PimCatalogTaxinomyBundle:Channel';
    }

}
