<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner\Fixtures;

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
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User'
            ),
            $configProvider
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
