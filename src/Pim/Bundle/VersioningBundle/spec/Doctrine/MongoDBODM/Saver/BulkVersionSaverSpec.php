<?php

namespace spec\Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\Saver;

use Akeneo\Component\Versioning\Model\Version;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @require Doctrine\MongoDB\Collection
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class BulkVersionSaverSpec extends ObjectBehavior
{
    function let(
        DocumentManager $documentManager,
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $this->beConstructedWith(
            $documentManager,
            $normalizer,
            'Akeneo\Component\Versioning\Model\Version'
        );

        $documentManager->getDocumentCollection('Akeneo\Component\Versioning\Model\Version')->willReturn($collection);
        $collection->getName()->willReturn('pim_versioning_version');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_several_versions(
        $normalizer,
        $collection,
        Version $version_a,
        Version $version_b
    ) {
        $normalizer->normalize($version_a, 'mongodb_document')->willReturn(['version_a_normalized']);
        $normalizer->normalize($version_b, 'mongodb_document')->willReturn(['version_b_normalized']);

        $collection->batchInsert(
            [
                ['version_a_normalized'],
                ['version_b_normalized']
            ]
        )->shouldBeCalled();

        $this->saveAll([$version_a, $version_b]);
    }
}
