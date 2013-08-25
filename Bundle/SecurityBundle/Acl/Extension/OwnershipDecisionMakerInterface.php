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
     * @param object $domainObject
     * @return bool
     */
    public function isOrganization($domainObject);

    /**
     * Determines whether the given domain object is a business unit
     *
     * @param object $domainObject
     * @return bool
     */
    public function isBusinessUnit($domainObject);

    /**
     * Determines whether the given domain object is an user
     *
     * @param object $domainObject
     * @return bool
     */
    public function isUser($domainObject);

    /**
     * Determines whether the given domain object is in the same organization as the given user
     * Furthermore, this method returns true if the the given domain object is the organization
     * the the given user belongs to.
     *
     * @param object $user
     * @param object $domainObject
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isBelongToOrganization($user, $domainObject);

    /**
     * Determines whether the given domain object is in the same business unit as the given user
     * Furthermore, this method returns true if the the given domain object is the business unit
     * the the given user belongs to.
     *
     * @param object $user
     * @param object $domainObject
     * @param boolean $deep Specify whether subordinate business units should be checked. Defaults to false.
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isBelongToBusinessUnit($user, $domainObject, $deep = false);

    /**
     * Determines whether the given domain object is belong to the given user.
     * Furthermore, this method returns true if the the given domain object and user are equal as well.
     *
     * @param object $user
     * @param object $domainObject
     * @return bool
     * @throws InvalidDomainObjectException
     */
    public function isBelongToUser($user, $domainObject);
}
