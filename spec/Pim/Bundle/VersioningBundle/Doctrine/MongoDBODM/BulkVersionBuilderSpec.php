<?php

namespace spec\Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Model\Version;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class BulkVersionBuilderSpec extends ObjectBehavior
{
    const VERSION_CLASS = 'Pim\Bundle\VersioningBundle\Model\Version';

    function let(
        VersionBuilder $versionBuilder,
        VersionContext $versionContext,
        DocumentManager $documentManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $versionBuilder,
            $versionContext,
            $documentManager,
            $eventDispatcher,
            self::VERSION_CLASS
        );
    }

    function it_builds_versions_for_several_versionable_objects(
        $versionBuilder,
        $documentManager,
        Collection $collection,
        Builder $qb,
        Query $query,
        VersionableInterface $versionable_a,
        VersionableInterface $versionable_b,
        VersionableInterface $versionable_c,
        Version $version_a,
        Version $version_b,
        Version $version_c
    ) {
        $documentManager->getDocumentCollection(self::VERSION_CLASS)->willReturn($collection);
        $collection->getName()->willReturn('pim_versioning_version');

        $documentManager->createQueryBuilder(Argument::any())->willReturn($qb);
        $qb->field(Argument::any())->willReturn($qb);
        $qb->equals(Argument::any())->willReturn($qb);
        $qb->limit(Argument::any())->willReturn($qb);
        $qb->sort(Argument::cetera())->willReturn($qb);
        $qb->getQuery(Argument::any())->willReturn($query);
        $query->getSingleResult()->willReturn(null);

        $versionBuilder->buildVersion($versionable_a, Argument::cetera())->willReturn($version_a);
        $versionBuilder->buildVersion($versionable_b, Argument::cetera())->willReturn($version_b);
        $versionBuilder->buildVersion($versionable_c, Argument::cetera())->willReturn($version_c);
        $version_a->getChangeset()->willReturn(['enabled' => ['old' => false, 'new' => true]]);
        $version_b->getChangeset()->willReturn(['enabled' => ['old' => false, 'new' => true]]);
        $version_c->getChangeset()->willReturn([]);

        $this
            ->buildVersions([$versionable_a, $versionable_b, $versionable_c])
            ->shouldReturn([$version_a, $version_b]);
    }

}
