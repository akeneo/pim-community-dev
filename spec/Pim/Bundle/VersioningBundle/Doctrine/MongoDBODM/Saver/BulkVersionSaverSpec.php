<?php

namespace spec\Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\Saver;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BulkVersionSaverSpec extends ObjectBehavior
{
    function let(
        DocumentManager $documentManager,
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher,
        Collection $collection,
        Builder $qb,
        Query $query
    ) {
        $this->beConstructedWith(
            $documentManager,
            $versionBuilder,
            $versionManager,
            $versionContext,
            $normalizer,
            $eventDispatcher,
            'Pim\Bundle\VersioningBundle\Model\Version'
        );

        $documentManager->getDocumentCollection('Pim\Bundle\VersioningBundle\Model\Version')->willReturn($collection);
        $collection->getName()->willReturn('pim_versioning_version');

        $documentManager->createQueryBuilder(Argument::any())->willReturn($qb);
        $qb->field(Argument::any())->willReturn($qb);
        $qb->equals(Argument::any())->willReturn($qb);
        $qb->limit(Argument::any())->willReturn($qb);
        $qb->sort(Argument::cetera())->willReturn($qb);
        $qb->getQuery(Argument::any())->willReturn($query);
        $query->getSingleResult()->willReturn(null);
    }

    function it_builds_and_saves_several_versionable_objects(
        $versionBuilder,
        $normalizer,
        $collection,
        ProductInterface $product_a,
        ProductInterface $product_b,
        ProductInterface $product_c,
        Version $version_a,
        Version $version_b,
        Version $version_c
    ) {
        $versionBuilder->buildVersion($product_a, Argument::cetera())->willReturn($version_a);
        $versionBuilder->buildVersion($product_b, Argument::cetera())->willReturn($version_b);
        $versionBuilder->buildVersion($product_c, Argument::cetera())->willReturn($version_c);
        $version_a->getChangeset()->willReturn(['enabled' => ['old' => false, 'new' => true]]);
        $version_b->getChangeset()->willReturn(['enabled' => ['old' => false, 'new' => true]]);
        $version_c->getChangeset()->willReturn([]);

        $product_a->getId()->willReturn('id_a');
        $product_b->getId()->willReturn('id_b');

        $normalizer->normalize($version_a, 'mongodb_document')->willReturn(['version_a_normalized']);
        $normalizer->normalize($version_b, 'mongodb_document')->willReturn(['version_b_normalized']);

        $collection->batchInsert(
            [
                ['version_a_normalized'],
                ['version_b_normalized']
            ]
        )
        ->shouldBeCalled();

        $this
            ->bulkSave([$product_a, $product_b])
            ->shouldReturn(['id_a', 'id_b']);
    }
}
