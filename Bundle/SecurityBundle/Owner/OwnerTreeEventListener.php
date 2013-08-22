<?php

namespace Oro\Bundle\SecurityBundle\Owner;

interface OwnerTreeEventListener
{
    public function loadUser(OwnerTree $ownerTree, $userId);
    public function loadBusinessUnit(OwnerTree $ownerTree, $businessUnitId);
    public function loadBusinessUnitHierarchy(OwnerTree $ownerTree, $organizationId);
}
