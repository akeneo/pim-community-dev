<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * A revision of an entity
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Revision
{
    /**
     * Get system id
     * @return integer $i
     */
    public abstract function getId();

    /**
     * Get date time
     * @return DateTime $datetime
     */
    public abstract function getDateTime();

    /**
     * Get author
     * @return TODO object ? id ?
     */
    public abstract function getAuthor();

    /**
     * Remove revision
     */
    public abstract function remove();

}
