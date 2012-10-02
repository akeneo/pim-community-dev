<?php

namespace Strixos\CatalogBundle\Entity\Behavior;

/**
 * 
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Strixos\CatalogBundle\Entity\Behavior\Translatable
 */
interface Versionnable
{
    /**
     * Get revision list
     * @return <Revision>
     */
    public function getRevisionList();
    
    /**
     * Switch to the requested revision
     * @param integer $revision
     */
    public function switchToRevision($revision);
    
    /**
     * Get head revision
     * @return Revision
     */
    public function getHeadRevision();
    
    /**
     * Create revision if $quiet is true else erase current one
     * @param string $quiet
     * 
     * TODO : bug because there is already a saving method in model
     */
    public function save($quiet = false);
    
    /**
     * Logical delete
     * Flag set to false
     */
    public function delete();
    
    /**
     * Physical delete
     */
    public function purge();
    
    /**
     * clone object
     */
    public function copy();
}