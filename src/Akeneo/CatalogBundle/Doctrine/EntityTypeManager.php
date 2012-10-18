<?php
namespace Akeneo\CatalogBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Entity type manager, a general doctrine implementation, not depends on storage (entity or document)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EntityTypeManager extends AbstractManager
{

    /**
     * Object type class
     * @var mixed
     */
    protected $typeClass;

    /**
    * Object class
    * @var mixed
    */
    protected $objectClass;

    /**
    * Load embedded entity type
    *
    * @param string $code
    * @return EntityTypeManager
    */
    public function find($code)
    {
        // get entity type
        $type = $this->repository->findOneByCode($code);
        if ($type) {
            $this->object = $type;
            return $this;
        } else {
            return null;
        }
    }

     /**
     * Create an embeded type entity
     * @param string $code
     * @param string $title
     * @return EntityTypeManager
     */
    public function create($code, $title = null)
    {
        // check if exists
        $type = $this->repository->findOneByCode($code);
        if ($type) {
            // TODO create custom exception
            throw new \Exception("There is already an entity type {$this->class} with the code {$code}");
        } else {
            $this->object = new $this->class();
            $this->object->setCode($code);
            if (!$title) {
                $title = $code;
            }
            $this->object->setTitle($title);
        }
        return $this;
    }

    /**
     * Get type object code
     * @return string code
     */
    public function getCode()
    {
        return $this->getObject()->getCode();
    }

    /**
     * Get product type title
     * @return string
     */
    public function getTitle()
    {
        return $this->object->getTitle();
    }

    /**
     * Set product type title
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->object->setTitle($title);
    }

}