<?php

namespace spec\Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PendingMassPersisterSpec extends ObjectBehavior
{
    function let(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        Connection $connection,
        EntityManager $entityManager,
        TableNameBuilder $tableNameBuilder
    ) {
        $this->beConstructedWith(
            $versionBuilder,
            $versionManager,
            $versionContext,
            $normalizer,
            'VersionClass',
            $connection,
            $entityManager,
            $tableNameBuilder
        );
    }

    function it_massively_persists_pending_versions(
        $versionBuilder,
        $versionManager,
        $normalizer,
        $connection,
        $entityManager,
        $tableNameBuilder,
        $versionContext,
        ClassMetadata $versionMetadata,
        ProductInterface $product1,
        ProductInterface $product2,
        Version $pendingVersion1,
        Version $pendingVersion2,
        \DateTime $date1,
        \DateTime $date2
    ) {
        $products = [$product1, $product2];

        $date1->format(\DateTime::ISO8601)->willReturn('2014-07-16T10:20:36+02:00');
        $date2->format(\DateTime::ISO8601)->willReturn('2014-07-16T10:20:37+02:00');

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

        $tableNameBuilder->getTableName('VersionClass')->willReturn('version_table');

        $versionMetadata->getColumnNames()->willReturn(
            ['id', 'author', 'changeset', 'snapshot', 'resource_name', 'resource_id', 'context', 'logged_at', 'pending']
        );
        $versionMetadata->getFieldName('author')->willReturn('author');
        $versionMetadata->getFieldName('changeset')->willReturn('changeset');
        $versionMetadata->getFieldName('snapshot')->willReturn('snapshot');
        $versionMetadata->getFieldName('resource_name')->willReturn('resourceName');
        $versionMetadata->getFieldName('resource_id')->willReturn('resourceId');
        $versionMetadata->getFieldName('context')->willReturn('context');
        $versionMetadata->getFieldName('logged_at')->willReturn('loggedAt');
        $versionMetadata->getFieldName('pending')->willReturn('pending');
        $versionMetadata->getIdentifierColumnNames()->willReturn(['id']);
        $versionMetadata->getFieldValue($pendingVersion1, 'author')->willReturn('julia');
        $versionMetadata->getFieldValue($pendingVersion1, 'context')->willReturn('CSV Import');
        $versionMetadata->getFieldValue($pendingVersion1, 'changeset')->willReturn(serialize($normalizedProduct1));
        $versionMetadata->getFieldValue($pendingVersion1, 'snapshot')->willReturn(null);
        $versionMetadata->getFieldValue($pendingVersion1, 'resourceName')->willReturn('ProductClass');
        $versionMetadata->getFieldValue($pendingVersion1, 'resourceId')->willReturn('myprod1');
        $versionMetadata->getFieldValue($pendingVersion1, 'loggedAt')->willReturn($date1);
        $versionMetadata->getFieldValue($pendingVersion1, 'pending')->willReturn(true);

        $versionMetadata->getFieldValue($pendingVersion2, 'author')->willReturn('julia');
        $versionMetadata->getFieldValue($pendingVersion2, 'context')->willReturn('CSV Import');
        $versionMetadata->getFieldValue($pendingVersion2, 'changeset')->willReturn(serialize($normalizedProduct2));
        $versionMetadata->getFieldValue($pendingVersion2, 'snapshot')->willReturn(null);
        $versionMetadata->getFieldValue($pendingVersion2, 'resourceName')->willReturn('ProductClass');
        $versionMetadata->getFieldValue($pendingVersion2, 'resourceId')->willReturn('myprod2');
        $versionMetadata->getFieldValue($pendingVersion2, 'loggedAt')->willReturn($date2);
        $versionMetadata->getFieldValue($pendingVersion2, 'pending')->willReturn(true);

        $entityManager->getClassMetadata('VersionClass')->willReturn($versionMetadata);

        $versionBuilder->createPendingVersion($product1, 'julia', $normalizedProduct1, 'CSV Import')
            ->willReturn($pendingVersion1);

        $versionBuilder->createPendingVersion($product2, 'julia', $normalizedProduct2, 'CSV Import')
            ->willReturn($pendingVersion2);

        $connection->executeQuery(
            'INSERT INTO version_table'.
            ' (author,changeset,snapshot,resource_name,resource_id,context,logged_at,pending)'.
            ' VALUES (?,?,?,?,?,?,?,?),(?,?,?,?,?,?,?,?)',
            [
                'julia',
                'a:2:{s:3:"sku";s:7:"sku-001";s:4:"name";s:12:"my product 1";}',
                null,
                'ProductClass',
                'myprod1',
                'CSV Import',
                '2014-07-16 08:20:36',
                true,
                'julia',
                'a:2:{s:3:"sku";s:7:"sku-002";s:4:"name";s:12:"my product 2";}',
                null,
                'ProductClass',
                'myprod2',
                'CSV Import',
                '2014-07-16 08:20:37',
                true
            ]
        )->shouldBeCalled();

        $this->persistPendingVersions($products);
    }
}
