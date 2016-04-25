<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $manager,
        $productValueClass,
        $attributeClass,
        AssetRepositoryInterface $assetRepository,
        $assetClass
    ) {
        $productClass = 'Pim\Component\Catalog\Model\ProductInterface';

        $this->beConstructedWith(
            $manager,
            $assetRepository,
            $productClass,
            $productValueClass,
            $attributeClass,
            $assetClass
        );
    }

    public function it_is_an_enterpriseCompletenessGenerator()
    {
        $this->shouldImplement('PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator');
    }

    public function it_can_schedule_completeness_for_an_asset(
        $assetRepository,
        $manager,
        AssetInterface $asset,
        ProductInterface $product1,
        ProductInterface $product2,
        Connection $connection,
        ClassMetadataInfo $classMetadata,
        Statement $statement
    ) {

        $sql = '
            DELETE c FROM pim_catalog_completeness c
            JOIN tableName p ON p.id = c.product_id
            WHERE p.id = :product_id';

        $product1->getId()->willReturn('1');
        $product2->getId()->willReturn('2');

        $assetRepository->findProducts($asset)->willReturn([$product1, $product2]);
        $manager->getConnection()->willReturn($connection);
        $manager->getClassMetadata('Pim\Component\Catalog\Model\ProductInterface')->willReturn($classMetadata);
        $classMetadata->getAssociationMapping(Argument::any())->shouldBeCalled();
        $manager->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->getTableName()->willReturn('tableName');
        $connection->prepare($sql)->willReturn($statement);
        $statement->bindValue('product_id', 1)->shouldBeCalled();
        $statement->bindValue('product_id', 2)->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $this->scheduleForAsset($asset);
    }
}
