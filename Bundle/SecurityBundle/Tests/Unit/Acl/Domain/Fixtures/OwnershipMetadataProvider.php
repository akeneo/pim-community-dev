<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider as OriginalOwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class OwnershipMetadataProvider extends OriginalOwnershipMetadataProvider
{
    private $metadata = array();

    public function __construct()
    {
        parent::__construct(
            array(
                'organization' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization',
                'business_unit' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit',
                'user' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User'
            )
        );
    }

    public function getMetadata($entityName)
    {
        return isset($this->metadata[$entityName])
            ? $this->metadata[$entityName]
            : new OwnershipMetadata();
    }

    public function setMetadata($entityName, OwnershipMetadata $metadata)
    {
        $this->metadata[$entityName] = $metadata;
    }
}
