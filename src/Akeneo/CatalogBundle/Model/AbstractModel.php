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
abstract class AbstractModel
{
    /**
     * @var ObjectManager $_objectManager
     */
    protected $_manager;

    /**
     * Main Entity or Document managed
     * @var mixed
     */
    protected $_object;

    /**
     * Used locale for embeded objects
     * @var string
     */
    private $_locale;

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
    public function getManager()
    {
        return $this->_manager;
    }

    /**
     * Return managed object
     *
     * TODO: should by protected ?
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Load encapsuled object
     * @param string $code
     * @return AbstractModel
     */
    public abstract function find($code);

    /**
    * Remove current embeded object from object manager
    */
    public function remove()
    {
        $this->getManager()->remove($this->getObject());
    }

    /**
     * Persist current embeded object
     * @return AbstractModel
     */
    public function persist()
    {
        $this->getManager()->persist($this->getObject());
        return $this;
    }

    /**
     * Flush modification of object manager on database
     * @return AbstractModel
     */
    public function flush()
    {
        $this->getManager()->flush();
        return $this;
    }

    /**
     * Change locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        // TODO
        $this->_locale = $locale;

    }

}