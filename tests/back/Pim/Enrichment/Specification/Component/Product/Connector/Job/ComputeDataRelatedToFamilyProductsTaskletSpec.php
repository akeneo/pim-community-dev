<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeDataRelatedToFamilyProductsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeDataRelatedToFamilyProductsTaskletSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        BulkSaverInterface $productSaver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $productQueryBuilderFactory,
            $familyReader,
            $productSaver,
            $cacheClearer,
            $jobRepository,
            $keepOnlyValuesForVariation,
            $validator,
            $productRepository,
            2
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeDataRelatedToFamilyProductsTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_saves_the_products_belonging_to_the_family(
        $familyReader,
        $familyRepository,
        $productSaver,
        $productQueryBuilderFactory,
        $jobRepository,
        $cacheClearer,
        ProductRepositoryInterface $productRepository,
        FamilyInterface $family,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor
    ) {
        $product1Uuid = Uuid::uuid4();
        $product2Uuid = Uuid::uuid4();
        $product3Uuid = Uuid::uuid4();

        $product1->isVariant()->willReturn(false);
        $product2->isVariant()->willReturn(false);
        $product3->isVariant()->willReturn(false);

        $familyReader->read()->willReturn(['code' => 'my_family'], null);
        $familyRepository->findOneByIdentifier('my_family')->willReturn($family);

        $family->getCode()->willReturn('family_code');

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn(
            new IdentifierResult('id1', ProductInterface::class, 'product_' . $product1Uuid->toString()),
            new IdentifierResult('id2', ProductInterface::class, 'product_' . $product2Uuid->toString()),
            new IdentifierResult('id3', ProductInterface::class, 'product_' . $product3Uuid->toString())
        );
        $cursor->next()->shouldBeCalled();
        $cursor->count()->shouldBeCalled()->willReturn(3);

        $productRepository->getItemsFromUuids([$product1Uuid->toString(), $product2Uuid->toString()])->willReturn([$product1, $product2]);
        $productRepository->getItemsFromUuids([$product3Uuid->toString()])->willReturn([$product3]);
        $productSaver->saveAll([$product1, $product2], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([$product3], ['force_save' => true])->shouldBeCalled();

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(3);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_saves_the_variant_products_belonging_to_the_family(
        $familyReader,
        $familyRepository,
        $productSaver,
        $productQueryBuilderFactory,
        $jobRepository,
        $cacheClearer,
        $keepOnlyValuesForVariation,
        $validator,
        ProductRepositoryInterface $productRepository,
        FamilyInterface $family,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        ProductInterface $variantProduct3,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor,
        ConstraintViolationListInterface $violationList1,
        ConstraintViolationListInterface $violationList2,
        ConstraintViolationListInterface $violationList3
    ) {
        $product1Uuid = Uuid::uuid4();
        $product2Uuid = Uuid::uuid4();
        $product3Uuid = Uuid::uuid4();

        $variantProduct1->isVariant()->willReturn(true);
        $variantProduct2->isVariant()->willReturn(true);
        $variantProduct3->isVariant()->willReturn(true);

        $familyReader->read()->willReturn(['code' => 'my_family'], null);
        $familyRepository->findOneByIdentifier('my_family')->willReturn($family);

        $family->getCode()->willReturn('family_code');

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn(
            new IdentifierResult('id1', ProductInterface::class, $product1Uuid->toString()),
            new IdentifierResult('id2', ProductInterface::class, $product2Uuid->toString()),
            new IdentifierResult('id3', ProductInterface::class, $product3Uuid->toString())
        );
        $cursor->next()->shouldBeCalled();
        $cursor->count()->shouldBeCalled()->willReturn(3);

        $productRepository->getItemsFromUuids([$product1Uuid->toString(), $product2Uuid->toString()])->willReturn([$variantProduct1, $variantProduct2]);
        $productRepository->getItemsFromUuids([$product3Uuid->toString()])->willReturn([$variantProduct3]);

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();

        $validator->validate($variantProduct1)->willReturn($violationList1);
        $violationList1->count()->willReturn(0);
        $validator->validate($variantProduct2)->willReturn($violationList2);
        $violationList2->count()->willReturn(0);
        $validator->validate($variantProduct3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productSaver->saveAll([$variantProduct1, $variantProduct2], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([$variantProduct3], ['force_save' => true])->shouldBeCalled();

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(3);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_saves_only_valid_variant_products_belonging_to_the_family(
        $familyReader,
        $familyRepository,
        $productSaver,
        $productQueryBuilderFactory,
        $jobRepository,
        $cacheClearer,
        $keepOnlyValuesForVariation,
        $validator,
        ProductRepositoryInterface $productRepository,
        FamilyInterface $family,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        ProductInterface $variantProduct3,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor,
        ConstraintViolationListInterface $violationList1,
        ConstraintViolationListInterface $violationList2,
        ConstraintViolationListInterface $violationList3
    ) {
        $product1Uuid = Uuid::uuid4();
        $product2Uuid = Uuid::uuid4();
        $product3Uuid = Uuid::uuid4();

        $variantProduct1->isVariant()->willReturn(true);
        $variantProduct2->isVariant()->willReturn(true);
        $variantProduct3->isVariant()->willReturn(true);

        $familyReader->read()->willReturn(['code' => 'my_family'], null);
        $familyRepository->findOneByIdentifier('my_family')->willReturn($family);

        $family->getCode()->willReturn('family_code');

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn(
            new IdentifierResult('id1', ProductInterface::class, 'product_' . $product1Uuid->toString()),
            new IdentifierResult('id2', ProductInterface::class, 'product_' . $product2Uuid->toString()),
            new IdentifierResult('id3', ProductInterface::class, 'product_' . $product3Uuid->toString())
        );
        $cursor->next()->shouldBeCalled();
        $cursor->count()->shouldBeCalled()->willReturn(3);

        $productRepository->getItemsFromUuids([$product1Uuid->toString(), $product2Uuid->toString()])->willReturn([$variantProduct1, $variantProduct2]);
        $productRepository->getItemsFromUuids([$product3Uuid->toString()])->willReturn([$variantProduct3]);

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();

        $validator->validate($variantProduct1)->willReturn($violationList1);
        $violationList1->count()->willReturn(1);
        $validator->validate($variantProduct2)->willReturn($violationList2);
        $violationList2->count()->willReturn(1);
        $validator->validate($variantProduct3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productSaver->saveAll([$variantProduct3], ['force_save' => true])->shouldBeCalled();

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(3);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(3);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_skips_if_the_family_is_unknown(
        $familyReader,
        $familyRepository,
        $productQueryBuilderFactory,
        $productSaver,
        $jobRepository,
        StepExecution $stepExecution
    ) {
        $familyReader->read()->willReturn(['code' => 'unkown_family'], null);
        $familyRepository->findOneByIdentifier('unkown_family')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $productSaver->saveAll(Argument::any(), Argument::any())->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_handles_invalid_lines(
        $familyReader,
        $familyRepository,
        $productQueryBuilderFactory,
        $productSaver,
        $jobRepository,
        StepExecution $stepExecution
    ) {
        $familyReader->read()->willThrow(InvalidItemException::class);
        $familyReader->read()->willReturn(null);

        $familyRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $productSaver->saveAll(Argument::any(), Argument::any())->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
