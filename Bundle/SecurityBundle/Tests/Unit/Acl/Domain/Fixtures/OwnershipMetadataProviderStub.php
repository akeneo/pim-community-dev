<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class OwnershipMetadataProviderStub extends OwnershipMetadataProvider
{
    private $metadata = array();

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $configProvider = $testCase->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        parent::__construct(
            array(
                'organization' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization',
                'business_unit' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit',
                'user' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User'
            ),
            $configProvider
        );
    }

    public function getMetadata($className)
    {
        return isset($this->metadata[$className])
            ? $this->metadata[$className]
            : new OwnershipMetadata();
    }

    public function setMetadata($className, OwnershipMetadata $metadata)
    {
        $this->metadata[$className] = $metadata;
    }
}
