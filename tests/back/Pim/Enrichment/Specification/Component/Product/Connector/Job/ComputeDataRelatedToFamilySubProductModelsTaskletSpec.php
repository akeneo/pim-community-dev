<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeDataRelatedToFamilySubProductModelsTasklet;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeDataRelatedToFamilySubProductModelsTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ItemReaderInterface $familyReader,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $queryBuilderFactory,
            $familyReader,
            $keepOnlyValuesForVariation,
            $validator,
            $productModelSaver,
            $cacheClearer,
            $jobRepository,
            2
        );

        $this->setStepExecution($stepExecution);
    }

    function it_computes_data_related_to_family_root_product_model()
    {
        $this->shouldBeAnInstanceOf(ComputeDataRelatedToFamilySubProductModelsTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldBeAnInstanceOf(TaskletInterface::class);
    }

    function it_is_initializable($cacheClearer)
    {
        $this->shouldBeAnInstanceOf(InitializableInterface::class);

        $cacheClearer->clear()->shouldBeCalled();

        $this->initialize();
    }

    function it_computes_no_product_models_if_there_is_nothingmore__to_read_in_the_imported_file(
        $cacheClearer,
        $familyReader
    ) {
        $familyReader->read()->willReturn(null);
        $cacheClearer->clear()->shouldBeCalledTimes(1);

        $this->execute();
    }

    function it_computes_no_product_models_if_imported_family_does_not_exists(
        $cacheClearer,
        $familyReader,
        $familyRepository,
        $stepExecution
    ) {
        $familyReader->read()->willReturn(['code' => 'family_code'], null);
        $familyRepository->findOneByIdentifier('family_code')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(1);

        $this->execute();
    }

    function it_computes_sub_product_models(
        $cacheClearer,
        $familyReader,
        $familyRepository,
        $keepOnlyValuesForVariation,
        $validator,
        $queryBuilderFactory,
        $productModelSaver,
        $jobRepository,
        $stepExecution,
        FamilyInterface $family,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ConstraintViolationListInterface $violationList1,
        ConstraintViolationListInterface $violationList2,
        ConstraintViolationListInterface $violationList3
    ) {
        $familyReader->read()->willReturn(['code' => 'family_code'], null);
        $familyRepository->findOneByIdentifier('family_code')->willReturn($family);
        $family->getCode()->willReturn('family_code');

        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn($productModel1, $productModel2, $productModel3);
        $cursor->next()->shouldBeCalled();

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel3])->shouldBeCalled();

        $validator->validate($productModel1)->willReturn($violationList1);
        $violationList1->count()->willReturn(0);
        $validator->validate($productModel2)->willReturn($violationList2);
        $violationList2->count()->willReturn(0);
        $validator->validate($productModel3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productModelSaver->saveAll([$productModel1, $productModel2])->shouldBeCalled();
        $productModelSaver->saveAll([$productModel3])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);
        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $this->execute();
    }

    function it_saves_only_valid_sub_product_models(
        $cacheClearer,
        $familyReader,
        $familyRepository,
        $keepOnlyValuesForVariation,
        $validator,
        $queryBuilderFactory,
        $productModelSaver,
        $jobRepository,
        $stepExecution,
        FamilyInterface $family,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ConstraintViolationListInterface $violationList1,
        ConstraintViolationListInterface $violationList2,
        ConstraintViolationListInterface $violationList3
    ) {
        $familyReader->read()->willReturn(['code' => 'family_code'], null);
        $familyRepository->findOneByIdentifier('family_code')->willReturn($family);
        $family->getCode()->willReturn('family_code');

        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn($productModel1, $productModel2, $productModel3);
        $cursor->next()->shouldBeCalled();

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel3])->shouldBeCalled();

        $validator->validate($productModel1)->willReturn($violationList1);
        $violationList1->count()->willReturn(1);
        $validator->validate($productModel2)->willReturn($violationList2);
        $violationList2->count()->willReturn(1);
        $validator->validate($productModel3)->willReturn($violationList3);
        $violationList3->count()->willReturn(0);

        $productModelSaver->saveAll([$productModel3])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(1);
        $cacheClearer->clear()->shouldBeCalledTimes(1);

        $this->execute();
    }
}
