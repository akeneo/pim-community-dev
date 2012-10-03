<?php
namespace Bap\FlexibleEntityBundle\Model;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Responsible of create flexible entity
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class FlexibleEntityManager extends ContainerAware
{

    /**
     * Shortcut to return the persistence object manager
     *
     * @return ObjectManager
     */
    public abstract function getObjectManager();

}