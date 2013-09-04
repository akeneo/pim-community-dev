<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

/**
 * The aim of this class is to provide a way to get details why IsGranted method decide
 * to allow or deny access to a resource.
 *
 * The "one-shot" prefix in the name of this class means that this observer is used once only.
 * In other words it can be used in one IsGranted operation only. After IsGranted operation
 * finished an observable object forgets this observer. It means that you should set
 * the observer before each IsGranted operation you want to get details for.
 */
class OneShotIsGrantedObserver
{
    /**
     * @var int
     */
    protected $accessLevel = AccessLevel::UNKNOWN;

    /**
     * Gets a value indicates in which scope the access to a resource is granted or denied
     *
     * @return int Can be one of AccessLevel::*_LEVEL constants
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * Sets a value indicates in which scope the access to a resource is granted or denied
     *
     * @param int $accessLevel Can be one of AccessLevel::*_LEVEL constants
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;
    }
}
