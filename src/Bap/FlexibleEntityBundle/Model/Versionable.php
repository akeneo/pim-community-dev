<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Interface to implements to make an entity versionable
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Versionable
{
    /**
    * Get revision list
     * @return TODO
    */
    public function getRevisionList();

    /**
    * Switch to revision
    * @param RevisionInterface $revision
    */
    public function switchRevision(RevisionInterface $revision);

    /**
    * Get head revision
    * @return RevisionInterface
    */
    public function getHeadRevision();
}
