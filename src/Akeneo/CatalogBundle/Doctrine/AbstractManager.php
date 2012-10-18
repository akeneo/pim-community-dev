<?php
namespace Akeneo\CatalogBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Abstract object manager, contains an entity and provide high level methods to play with flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractManager
{
    /**
    * @var ObjectManager $manager
    */
    protected $manager;

    /**
     * object class
     * @var string $class
     */
    protected $class;

    /**
     * object repository
     * @var str
     */
    protected $repository;

    /**
     * Main Entity or Document managed
     * @var mixed
     */
    protected $object;

    /**
     * Used locale for embeded objects
     *
     * TODO: useless ? based on object locale ?
     * @var string
     */
    private $locale;

    /**
     * Constructor.
     *
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->manager = $om;
        $this->repository = $om->getRepository($class);
        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Return managed object
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Remove current embeded object from object manager
     */
    public function remove()
    {
        $this->getManager()->remove($this->getObject());
    }

    /**
     * Persist current embeded object
     * @return AbstractManager
     */
    public function persist()
    {
        $this->getManager()->persist($this->getObject());
        return $this;
    }

    /**
     * Flush modification of object manager on database
     * @return AbstractManager
     */
    public function flush()
    {
        $this->getManager()->flush();
        return $this;
    }

    /**
     * Refresh type state from database
     * @return AbstractManager
     */
    public function refresh()
    {
        $this->getManager()->refresh($this->getObject());
        return $this;
    }

    /**
     * Get product locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Change locale and refresh data for this locale
     *
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->locale = $locale;
    }
}