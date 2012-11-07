<?php
namespace Bap\Bundle\FlexibleEntityBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class FlexibleEntityManager
{
    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * Constructor.
     *
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(ObjectManager $om)
    {
        $this->manager = $om;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getPersistenceManager()
    {
        return $this->manager;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getEntityShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getTypeShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getGroupShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public abstract function getFieldShortname();

    /**
     * Return implmentation class that can be use to instanciate
     * @return string
     */
    public function getEntityClass()
    {
        return $this->manager->getClassMetadata($this->getEntityShortname())->getName();
    }

    /**
     * Return implmentation class that can be use to instanciate
     * @return string
     */
    public function getTypeClass()
    {
        return $this->manager->getClassMetadata($this->getTypeShortname())->getName();
    }

    /**
     * Return implmentation class that can be use to instanciate
     * @return string
     */
    public function getGroupClass()
    {
        return $this->manager->getClassMetadata($this->getGroupShortname())->getName();
    }

    /**
     * Return implmentation class that can be use to instanciate
     * @return string
     */
    public function getFieldClass()
    {
        return $this->manager->getClassMetadata($this->getFieldShortname())->getName();
    }

}