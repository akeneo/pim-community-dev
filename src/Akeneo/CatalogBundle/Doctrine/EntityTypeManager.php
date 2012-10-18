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
     * Get type object code
     * @return string code
     */
    public function getCode()
    {
        return $this->getObject()->getCode();
    }
}