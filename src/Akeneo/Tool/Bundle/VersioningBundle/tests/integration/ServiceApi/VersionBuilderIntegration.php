<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\ServiceApi;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilderIntegration extends TestCase
{
    private VersionFactory $versionFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->versionFactory = $this->get('pim_versioning.factory.version');
    }

    public function testBuildVersionOnCategoryCreation(): void
    {
        $versionRepositoryMock = $this->createMock(VersionRepository::class);
        $versionRepositoryMock->method('getNewestLogEntry')->willReturn(null);

        $givenAuthor = 'julia';
        $givenPreBuildVersionEvent = (new BuildVersionEvent())->setUsername($givenAuthor);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherMock
            ->method('dispatch')
            ->with(new BuildVersionEvent(), BuildVersionEvents::PRE_BUILD)
            ->willReturn($givenPreBuildVersionEvent)
        ;

        $versionBuilder = new VersionBuilder(
            $this->versionFactory,
            $versionRepositoryMock,
            $eventDispatcherMock
        );

        $givenSnapshot = [
            'code' => 'photo',
            'parent' => 'master',
            'updated' => '2023-01-16T14:30:30+00:00',
            'label-en_US' => 'photo',
        ];

        $givenChangeSet = [
            'code' => [
                'old' => '',
                'new' => 'photo',
            ],
            'parent' => [
                'old' => '',
                'new' => 'master',
            ],
            'updated' => [
                'old' => '',
                'new' => '2023-01-16T14:30:30+00:00',
            ],
            'label-en_US' => [
                'old' => '',
                'new' => 'photo'
            ]
        ];

        $givenResourceName = 'Akeneo\Category\Infrastructure\Component\Model\Category';

        $version = $versionBuilder->buildVersionWithId(
            resourceId: null,
            resourceName: $givenResourceName,
            snapshot: $givenSnapshot,
        );

        $expectedVersion = $this->versionFactory->create($givenResourceName, null, null, $givenAuthor, null);
        $expectedVersion->setVersion(1)
            ->setSnapshot($givenSnapshot)
            ->setChangeset($givenChangeSet);

        $this->assertVersion($expectedVersion, $version);
    }

    /**
     * Assert all fields except loggedAt due to different timestamp between expected and actual time
     */
    private function assertVersion(Version $expectedVersion, Version $version): void
    {
        $this->assertEquals($expectedVersion->getResourceId(), $version->getResourceId());
        $this->assertEquals($expectedVersion->getResourceUuid(), $version->getResourceUuid());
        $this->assertEquals($expectedVersion->getVersion(), $version->getVersion());
        $this->assertEquals($expectedVersion->getResourceName(), $version->getResourceName());
        $this->assertEquals($expectedVersion->getAuthor(), $version->getAuthor());
        $this->assertEquals($expectedVersion->getSnapshot(), $version->getSnapshot());
        $this->assertEquals($expectedVersion->getChangeset(), $version->getChangeset());
        $this->assertEquals($expectedVersion->getContext(), $version->getContext());
        $this->assertEquals($expectedVersion->isPending(), $version->isPending());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
