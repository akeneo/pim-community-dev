<?php
namespace Bap\Bundle\FlexibleEntityBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Entity manager, a general doctrine implementation, not depends on storage (entity or document)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityManager extends AbstractManager
{

    /**
     * Load encapsuled entity
     * @param integer $entityId
     * @return EntityTypeManager
     */
    public abstract function find($entityId);

    /**
     * Create an embeded type entity
     * @param string $type
     * @return EntityTypeManager
     */
    public abstract function create($type);

    /**
     * Get entity value for a field code
     *
     * @param string $fieldCode
     * @return mixed
     */
    public abstract function getValue($fieldCode);

    /**
     * Set entity value for a field
     *
     * @param string $fieldCode
     * @param string $data
     */
    public abstract function setValue($fieldCode, $data);

    /**
    * Adds support for magic getter / setter.
    *
    * @return array|object The found entity/entities.
    * @throws BadMethodCallException  If the method called is an invalid find* method
    *                                 or no find* method at all and therefore an invalid
    *                                 method call.
    */
    public function __call($method, $arguments)
    {
        // check if method is getField or setField
        switch (true) {
            // getValue(code)
            case (0 === strpos($method, 'get')):
                $by = substr($method, 3);
                $method = 'getValue';
                $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));
                return $this->$method($fieldName);
                break;
                // setValue(code, value)
            case (0 === strpos($method, 'set')):
                $by = substr($method, 3);
                $method = 'setValue';
                $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));
                return $this->$method($fieldName, $arguments[0]);
                break;
        }
    }
}