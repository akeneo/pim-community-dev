<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Cleaner;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MassEditVariantGroupCleanerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        MassEditRepositoryInterface $massEditRepository,
        ObjectManager $objectManager,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $paginatorFactory,
            $objectDetacher,
            $massEditRepository,
            $objectManager,
            $groupRepository,
            $productRepository,
            $translator
        );
    }

    function it_executes_with_no_products(
        $groupRepository,
        $productRepository,
        $massEditRepository,
        $objectManager,
        $paginatorFactory,
        $pqbFactory,
        GroupInterface $variantGroup,
        ProductQueryBuilderInterface $productQueryBuilder,
        StepExecution $stepExecution,
        CursorInterface $cursor,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf,
        PaginatorInterface $paginator
    ) {
        $this->setStepExecution($stepExecution);
        $configuration = [
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2], 'context' => []]],
            'actions' => [['value' => 'variant_group_code']]
        ];

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);

        $variantGroup->getId()->willReturn(42);

        $productRepository->getEligibleProductIdsForVariantGroup(42)->willReturn([1, 2, 3, 4]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);
        $massEditJobConf->setConfiguration(json_encode(null))->shouldBeCalled();

        $pqbFactory->create()->willReturn($productQueryBuilder);

        $productQueryBuilder->addFilter('id', 'IN', [1, 2], [])->shouldBeCalled();
        $productQueryBuilder->execute()->willReturn($cursor);

        $objectManager->persist($massEditJobConf)->shouldBeCalled();
        $objectManager->flush($massEditJobConf)->shouldBeCalled();

        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);


        $this->execute($configuration);
    }

    function it_executes(
        $groupRepository,
        $productRepository,
        $massEditRepository,
        $objectManager,
        $paginatorFactory,
        $pqbFactory,
        $translator,
        GroupInterface $variantGroup,
        ProductQueryBuilderInterface $productQueryBuilder,
        StepExecution $stepExecution,
        CursorInterface $cursor,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4
    ) {
        $this->setStepExecution($stepExecution);
        $configuration = [
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2, 3, 4], 'context' => []]],
            'actions' => [['value' => 'variant_group_code']]
        ];

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);

        $variantGroup->getId()->willReturn(42);

        $productRepository->getEligibleProductIdsForVariantGroup(42)->willReturn([1, 2, 3]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);

        $translator->trans('add_to_variant_group_clean.steps.add_to_variant_group.steps.cleaner.warning.title.title')
            ->willReturn('Duplicated variant Axis');

        $translator->trans('add_to_variant_group.steps.cleaner.warning.description')
            ->willReturn('Product can\'t be set in the selected variant group: duplicate variation axis values with another product in selection');


        $stepExecution->incrementSummaryInfo('skipped_product')->shouldBeCalledTimes(1);
        $stepExecution->addWarning(
            'Duplicated variant Axis',
            'Product can\'t be set in the selected variant group: duplicate variation axis values with' .
            ' another product in selection',
            [],
            $product4
        )->shouldBeCalledTimes(1);

        $pqbFactory->create()->willReturn($productQueryBuilder);

        $productQueryBuilder->addFilter('id', 'IN', [1, 2, 3, 4], [])->shouldBeCalled();
        $productQueryBuilder->execute()->willReturn($cursor);

        $objectManager->persist($massEditJobConf)->shouldBeCalled();
        $objectManager->flush($massEditJobConf)->shouldBeCalled();

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);
        $product3->getId()->willReturn(3);
        $product4->getId()->willReturn(4);

        $massEditJobConf->setConfiguration(json_encode([
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2, 3]]],
            'actions' => [['value' => 'variant_group_code']]
        ]))->shouldBeCalled();

        $productPage = [$product1, $product2, $product3, $product4];

        $paginatorFactory->createPaginator($cursor)->willReturn(
            [$productPage]
        );

        $this->execute($configuration);
    }
}
