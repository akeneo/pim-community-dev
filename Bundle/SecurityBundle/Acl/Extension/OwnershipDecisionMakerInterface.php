<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

/**
 * Provides an interface which should be implemented by a class
 * which makes decisions based on ownership of domain objects.
 */
interface OwnershipDecisionMakerInterface
{
    /**
     * Determines whether the given domain object is an organization
     *
     * @param  object $domainObject
     * @return bool
     */
    public function isOrganization($domainObject);

    /**
     * Determines whether the given domain object is a business unit
     *
     * @param  object $domainObject
     * @return bool
     */
    public function isBusinessUnit($domainObject);

    /**
     * Determines whether the given domain object is an user
     *
     * @param  object $domainObject
     * @return bool
     */
    public function isUser($domainObject);

    /**
     * Determines whether the given domain object is associated with
     * an any organization of the given user
     *
     * @param  object                       $user
     * @param  object                       $domainObject
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isAssociatedWithOrganization($user, $domainObject);

    /**
     * Determines whether the given domain object is associated with
     * an any business unit of the given user
     *
     * @param  object  $user
     * @param  object  $domainObject
     * @param  boolean $deep Specify whether subordinate business units should be checked. Defaults to false.
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isAssociatedWithBusinessUnit($user, $domainObject, $deep = false);

    /**
     * Determines whether the given domain object is associated with the given user
     *
     * @param  object                       $user
     * @param  object                       $domainObject
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isAssociatedWithUser($user, $domainObject);
}
