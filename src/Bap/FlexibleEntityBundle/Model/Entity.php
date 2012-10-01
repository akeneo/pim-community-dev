<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Entity
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Entity
{

    /**
     * Get system id
     * @return integer $i
     */
    public  abstract function getId();

    /**
     * Persist entity, create a revision by default, then erase current revision
     * @param boolean $quietMode
     * TODO in entity manager ?
     *
    public function save(boolean $quietMode);

    /**
     * Logical delete
     * TODO in entity manager ?
     *
    public function delete();

    /**
     * Physical delete
     * TODO in entity manager ?
     *
    public function purge();
    */

}