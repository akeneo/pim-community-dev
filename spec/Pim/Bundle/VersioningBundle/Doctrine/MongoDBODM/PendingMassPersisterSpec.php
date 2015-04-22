<?php

namespace spec\Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Bundle\TransformBundle\Normalizer\MongoDB\VersionNormalizer;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class PendingMassPersisterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\PendingMassPersister');
    }

    function let(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        DocumentManager $manager
    ) {
        $this->beConstructedWith(
            $versionBuilder,
            $versionManager,
            $versionContext,
            $normalizer,
            'VersionClass',
            $manager
        );
    }

    function it_massively_persists_pending_versions(
        $versionBuilder,
        $versionManager,
        $versionContext,
        $normalizer,
        $manager,
        ProductInterface $product1,
        ProductInterface $product2,
        Version $pendingVersion1,
        Version $pendingVersion2,
        Collection $collection
    ) {
        $products = [$product1, $product2];

        $versionManager->getUsername()->willReturn('julia');
        $versionContext->getContextInfo()->willReturn('CSV Import');

        $normalizedProduct1 = [
            'sku'  => 'sku-001',
            'name' => 'my product 1'
        ];
        $normalizedProduct2 = [
            'sku'  => 'sku-002',
            'name' => 'my product 2'
        ];

        $normalizer->normalize($product1, 'csv', ['versioning' => true])->willReturn($normalizedProduct1);
        $normalizer->normalize($product2, 'csv', ['versioning' => true])->willReturn($normalizedProduct2);

        $versionBuilder->createPendingVersion($product1, 'julia', $normalizedProduct1, 'CSV Import')
            ->willReturn($pendingVersion1);

        $versionBuilder->createPendingVersion($product2, 'julia', $normalizedProduct2, 'CSV Import')
            ->willReturn($pendingVersion2);

        $mongoVersions = [$normalizedProduct1, $normalizedProduct2];
        $normalizer->normalize($pendingVersion1, VersionNormalizer::FORMAT)->willReturn($normalizedProduct1);
        $normalizer->normalize($pendingVersion2, VersionNormalizer::FORMAT)->willReturn($normalizedProduct2);

        $manager->getDocumentCollection('VersionClass')->willReturn($collection);

        $collection->batchInsert($mongoVersions)->shouldBeCalled();

        $this->persistPendingVersions($products);
    }
}
