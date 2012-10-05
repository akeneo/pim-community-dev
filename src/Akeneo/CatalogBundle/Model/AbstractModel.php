<?php
namespace Akeneo\CatalogBundle\Model;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Abstract model
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AbstractModel
{
    /**
     * @var ObjectManager $_objectManager
     */
    protected $_manager;

    /**
    * Aims to inject object manager
    *
    * @param ObjectManager $objectManager
    */
    public function __construct($objectManager)
    {
        $this->_manager = $objectManager;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->_manager;
    }

}