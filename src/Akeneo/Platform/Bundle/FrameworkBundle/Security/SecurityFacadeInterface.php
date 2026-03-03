<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\Security;

interface SecurityFacadeInterface
{
    /**
     * Checks if an access to a resource is granted to the caller
     *
     * @param string|string[] $attributes Can be a role name(s), permission name(s), an ACL annotation id
     *                                    or something else, it depends on registered security voters
     * @param mixed           $object     A domain object, object identity or object identity descriptor (id:type)
     */
    public function isGranted($attributes, $object = null): bool;
}
