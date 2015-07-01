<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Item\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class VariantGroupCleanerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator,
        SaverInterface $saver
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $paginatorFactory,
            $objectDetacher,
            $jobConfigurationRepo,
            $groupRepository,
            $productRepository,
            $translator,
            $saver
        );
    }

    function it_cleans_products_that_are_not_eligible(
        $groupRepository,
        $productRepository,
        $jobConfigurationRepo,
        $paginatorFactory,
        $pqbFactory,
        $saver,
        GroupInterface $variantGroup,
        ProductQueryBuilderInterface $productQueryBuilder,
        StepExecution $stepExecution,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        JobExecution $jobExecution,
        TranslatorInterface $translator,
        JobConfigurationInterface $jobConfiguration,
        AttributeInterface $attribute1,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $this->setStepExecution($stepExecution);
        $configuration = [
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2], 'context' => []]],
            'actions' => ['value' => 'variant_group_code'],
        ];

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);

        $variantGroup->getId()->willReturn(42);
        $variantGroup->getAxisAttributes()->willReturn([$attribute1]);

        $attribute1->getCode()->willReturn('code_1');

        $productRepository->getEligibleProductIdsForVariantGroup(42)->willReturn([5, 6, 7]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $jobConfiguration->setConfiguration(json_encode(null))->shouldBeCalled();

        $pqbFactory->create()->willReturn($productQueryBuilder);

        $productQueryBuilder->addFilter('id', 'IN', [1, 2], [])->shouldBeCalled();
        $productQueryBuilder->execute()->willReturn($cursor);

        $translator->trans('pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid')
            ->willReturn('You cannot group the following product because it is already in a variant group or doesn\'t'.
                ' have the group axis.');

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);
        $stepExecution->addWarning(
            'excluded',
            'You cannot group the following product because it is already in a variant group or doesn\'t have the'.
            ' group axis.',
            [],
            Argument::any()
        )->shouldBeCalledTimes(2);

        $saver->save($jobConfiguration)->shouldBeCalled();

        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);
        $paginator->rewind()->willReturn();
        $paginator->count()->willReturn(1);
        $paginator->valid()->willReturn(true, false);
        $paginator->next()->willReturn();
        $paginator->current()->willReturn([$product1, $product2]);

        $this->execute($configuration);
    }

    function it_checks_if_products_have_duplicated_axis(
        $groupRepository,
        $productRepository,
        $jobConfigurationRepo,
        $saver,
        $paginatorFactory,
        $pqbFactory,
        GroupInterface $variantGroup,
        ProductQueryBuilderInterface $productQueryBuilder,
        StepExecution $stepExecution,
        CursorInterface $cursor,
        JobExecution $jobExecution,
        PaginatorInterface $paginator,
        JobConfigurationInterface $jobConfiguration,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        AttributeInterface $attribute1,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2,
        ProductValueInterface $productValue3,
        ProductValueInterface $productValue4,
        AttributeOptionInterface $attributeOption1,
        AttributeOptionInterface $attributeOption2,
        AttributeOptionInterface $attributeOption3,
        AttributeOptionInterface $attributeOption4
    ) {
        $this->setStepExecution($stepExecution);
        $configuration = [
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2, 3, 4], 'context' => []]],
            'actions' => ['value' => 'variant_group_code'],
        ];

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);

        $variantGroup->getId()->willReturn(42);
        $variantGroup->getAxisAttributes()->willReturn([$attribute1]);

        $attributeOption1->getCode()->willReturn('option_code_1');
        $productValue1->getData()->willReturn($attributeOption1);

        $attributeOption2->getCode()->willReturn('option_code_2');
        $productValue2->getData()->willReturn($attributeOption2);

        $attributeOption3->getCode()->willReturn('option_code_3');
        $productValue3->getData()->willReturn($attributeOption3);

        $attributeOption4->getCode()->willReturn('option_code_4');
        $productValue4->getData()->willReturn($attributeOption4);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);
        $product3->getId()->willReturn(3);
        $product4->getId()->willReturn(4);

        $attribute1->getCode()->willReturn('code_1');
        $product1->getValue('code_1')->willReturn($productValue1);

        $attribute1->getCode()->willReturn('code_1');
        $product2->getValue('code_1')->willReturn($productValue2);

        $attribute1->getCode()->willReturn('code_1');
        $product3->getValue('code_1')->willReturn($productValue3);

        $attribute1->getCode()->willReturn('code_1');
        $product4->getValue('code_1')->willReturn($productValue4);

        $productRepository->getEligibleProductIdsForVariantGroup(42)->willReturn([1, 2, 3, 4]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $pqbFactory->create()->willReturn($productQueryBuilder);

        $productQueryBuilder->addFilter('id', 'IN', [1, 2, 3, 4], [])->shouldBeCalled();
        $productQueryBuilder->execute()->willReturn($cursor);

        $saver->save($jobConfiguration)->shouldBeCalled();

        $jobConfiguration->setConfiguration(json_encode([
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2, 3, 4]]],
            'actions' => ['value'  => 'variant_group_code']
        ]))->shouldBeCalled();

        $productPage = [$product1, $product2, $product3, $product4];

        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);
        $paginator->rewind()->willReturn();
        $paginator->count()->willReturn(1);
        $paginator->valid()->willReturn(true, false);
        $paginator->next()->willReturn();
        $paginator->current()->willReturn($productPage);

        $this->execute($configuration);
    }

    function it_cleans_products_with_duplicated_axis(
        $groupRepository,
        $productRepository,
        $jobConfigurationRepo,
        $saver,
        $paginatorFactory,
        $pqbFactory,
        GroupInterface $variantGroup,
        ProductQueryBuilderInterface $productQueryBuilder,
        StepExecution $stepExecution,
        CursorInterface $cursor,
        JobExecution $jobExecution,
        PaginatorInterface $paginator1,
        PaginatorInterface $paginator2,
        TranslatorInterface $translator,
        JobConfigurationInterface $jobConfiguration,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        AttributeInterface $attribute1,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2,
        ProductValueInterface $productValue3,
        ProductValueInterface $productValue4,
        AttributeOptionInterface $attributeOption1,
        AttributeOptionInterface $attributeOption2,
        AttributeOptionInterface $attributeOption3,
        AttributeOptionInterface $attributeOption4
    ) {
        $this->setStepExecution($stepExecution);
        $configuration = [
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [1, 2, 3, 4], 'context' => []]],
            'actions' => ['value' => 'variant_group_code'],
        ];

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);

        $variantGroup->getId()->willReturn(42);
        $variantGroup->getAxisAttributes()->willReturn([$attribute1]);

        $attributeOption1->getCode()->willReturn('option_code_1');
        $productValue1->getData()->willReturn($attributeOption1);

        $attributeOption2->getCode()->willReturn('option_code_1');
        $productValue2->getData()->willReturn($attributeOption2);

        $attributeOption3->getCode()->willReturn('option_code_3');
        $productValue3->getData()->willReturn($attributeOption3);

        $attributeOption4->getCode()->willReturn('option_code_4');
        $productValue4->getData()->willReturn($attributeOption4);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);
        $product3->getId()->willReturn(3);
        $product4->getId()->willReturn(4);

        $attribute1->getCode()->willReturn('code_1');

        $product1->getValue('code_1')->willReturn($productValue1);
        $product2->getValue('code_1')->willReturn($productValue2);
        $product3->getValue('code_1')->willReturn($productValue3);
        $product4->getValue('code_1')->willReturn($productValue4);

        $productRepository->getEligibleProductIdsForVariantGroup(42)->willReturn([1, 2, 3, 4]);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);

        $pqbFactory->create()->willReturn($productQueryBuilder);

        $translator->trans('add_to_variant_group.steps.cleaner.warning.description')
            ->willReturn('Product can\'t be set in the selected variant group: duplicate variation axis values with'.
                ' another product in selection');

        $productPage = [$product1, $product2, $product3, $product4];
        $excludedProducts = [$product1, $product2];
        $paginatorFactory->createPaginator($cursor)->willReturn($paginator1, $paginator2);

        $paginator1->rewind()->willReturn();
        $paginator1->count()->willReturn(1);
        $paginator1->valid()->willReturn(true, false);
        $paginator1->next()->willReturn();
        $paginator1->current()->willReturn($productPage);

        $paginator2->rewind()->willReturn();
        $paginator2->count()->willReturn(1);
        $paginator2->valid()->willReturn(true, false);
        $paginator2->next()->willReturn();
        $paginator2->current()->willReturn($excludedProducts);

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);
        $stepExecution->addWarning(
            'duplicated',
            'Product can\'t be set in the selected variant group: duplicate variation axis values with another product'.
            ' in selection',
            [],
            Argument::any()
        )->shouldBeCalledTimes(2);

        $productQueryBuilder->addFilter('id', 'IN', [1, 2, 3, 4], [])->shouldBeCalledTimes(1);
        $productQueryBuilder->addFilter('id', 'IN', [1, 2], ['locale' => null, 'scope' => null])->shouldBeCalledTimes(1);
        $productQueryBuilder->execute()->willReturn($cursor);

        $saver->save($jobConfiguration)->shouldBeCalled();

        $jobConfiguration->setConfiguration(json_encode([
            'filters' => [['field' => 'id', 'operator' => 'IN', 'value' => [2 => 3, 3 => 4]]],
            'actions' => ['value'  => 'variant_group_code']
        ]))->shouldBeCalled();

        $this->execute($configuration);
    }
}
