<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Job\ComputeDataRelatedToFamilyVariantsTasklet;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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
        ValidatorInterface $validator
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
            2
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeDataRelatedToFamilyProductsTasklet::class);
    }

    function it_saves_the_products_belonging_to_the_family(
        $familyReader,
        $familyRepository,
        $productSaver,
        $productQueryBuilderFactory,
        $jobRepository,
        $cacheClearer,
        FamilyInterface $family,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor
    ) {
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
        $cursor->current()->willReturn($product1, $product2, $product3);
        $cursor->next()->shouldBeCalled();

        $productSaver->saveAll([$product1, $product2])->shouldBeCalled();
        $productSaver->saveAll([$product3])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(2);

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
        $cursor->current()->willReturn($variantProduct1, $variantProduct2, $variantProduct3);
        $cursor->next()->shouldBeCalled();


        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();

        $validator->validate($variantProduct1)->willReturn($violationList1);
        $violationList1->count()->willReturn(0);
        $validator->validate($variantProduct2)->willReturn($violationList2);
        $violationList2->count()->willReturn(0);
        $validator->validate($variantProduct3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productSaver->saveAll([$variantProduct1, $variantProduct2])->shouldBeCalled();
        $productSaver->saveAll([$variantProduct3])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(2);

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
        $cursor->current()->willReturn($variantProduct1, $variantProduct2, $variantProduct3);
        $cursor->next()->shouldBeCalled();


        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();

        $validator->validate($variantProduct1)->willReturn($violationList1);
        $violationList1->count()->willReturn(1);
        $validator->validate($variantProduct2)->willReturn($violationList2);
        $violationList2->count()->willReturn(1);
        $validator->validate($variantProduct3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productSaver->saveAll([$variantProduct3])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(1);

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
        $productSaver->saveAll(Argument::any())->shouldNotBeCalled();

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
        $productSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
