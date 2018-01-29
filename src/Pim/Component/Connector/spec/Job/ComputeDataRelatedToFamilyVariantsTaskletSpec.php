<?php

declare(strict_types=1);

namespace spec\Pim\Component\Connector\Job;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Connector\Job\ComputeDataRelatedToFamilyVariantsTasklet;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeDataRelatedToFamilyVariantsTaskletSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        BulkSaverInterface $productModelSaver,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $productModelQueryBuilderFactory,
            $familyReader,
            $keepOnlyValuesForVariation,
            $validator,
            $productModelSaver,
            $productSaver,
            $objectDetacher,
            $cacheClearer,
            $jobRepository
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeDataRelatedToFamilyVariantsTasklet::class);
    }

    function it_saves_the_product_model_and_its_descendants_belonging_to_the_family(
        $familyReader,
        $familyRepository,
        $keepOnlyValuesForVariation,
        $validator,
        $productModelSaver,
        $productSaver,
        $productModelQueryBuilderFactory,
        $jobRepository,
        $objectDetacher,
        FamilyInterface $family,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel,
        VariantProductInterface $product,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor,
        ArrayCollection $subProductModels,
        ArrayCollection $products,
        ConstraintViolationListInterface $rootProductModelViolationLists,
        ConstraintViolationListInterface $subProductModelViolationLists,
        ConstraintViolationListInterface $productViolationLists
    ) {
        $familyReader->read()->willReturn(['code' => 'my_family'], null);
        $familyRepository->findOneByIdentifier('my_family')->willReturn($family);

        $family->getCode()->willReturn('family_code');

        $productModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, false);
        $cursor->next()->willReturn($rootProductModel);
        $cursor->current()->willReturn($rootProductModel);

        $rootProductModel->hasProductModels()->willReturn(true);
        $rootProductModel->getProductModels()->willReturn($subProductModels);

        $subProductModels->toArray()->willReturn([$subProductModel]);
        $subProductModel->hasProductModels()->willReturn(false);
        $subProductModel->getProducts()->willReturn($products);

        $products->isEmpty()->willReturn(false);
        $products->toArray()->willReturn([$product]);

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])->shouldBeCalled();

        $rootProductModelViolationLists->count()->willReturn(0);
        $subProductModelViolationLists->count()->willReturn(0);
        $productViolationLists->count()->willReturn(0);

        $validator->validate($rootProductModel)->willReturn($rootProductModelViolationLists);
        $validator->validate($subProductModel)->willReturn($subProductModelViolationLists);
        $validator->validate($product)->willReturn($productViolationLists);

        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();
        $productSaver->saveAll([$product])->shouldBeCalled();

        $objectDetacher->detach($rootProductModel)->shouldBeCalled();
        $objectDetacher->detach($subProductModel)->shouldBeCalled();
        $objectDetacher->detach($product)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(3);
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_saves_the_product_models_and_its_descendants_belonging_to_the_families(
        $familyReader,
        $familyRepository,
        $keepOnlyValuesForVariation,
        $validator,
        $productModelSaver,
        $productSaver,
        $productModelQueryBuilderFactory,
        $jobRepository,
        $objectDetacher,
        FamilyInterface $family1,
        FamilyInterface $family2,
        ProductModelInterface $rootProductModel1,
        ArrayCollection $subProductModelCollection1,
        ArrayCollection $productCollection1,
        ArrayCollection $productCollection2,
        ProductModelInterface $subProductModel1,
        ProductModelInterface $rootProductModel2,
        VariantProductInterface $product1,
        VariantProductInterface $product2,
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb1,
        ProductAndProductModelQueryBuilder $pqb2,
        CursorInterface $cursor1,
        CursorInterface $cursor2,
        ConstraintViolationListInterface $rootProductModelViolationLists1,
        ConstraintViolationListInterface $rootProductModelViolationLists2,
        ConstraintViolationListInterface $subProductModelViolationLists1,
        ConstraintViolationListInterface $productViolationLists1,
        ConstraintViolationListInterface $productViolationLists2
    ) {
        $familyReader->read()->willReturn(['code' => 'first_family'], ['code' => 'second_family'], null);
        $familyRepository->findOneByIdentifier('first_family')->willReturn($family1);

        $family1->getCode()->willReturn('first_family');
        $family2->getCode()->willReturn('second_family');

        $productModelQueryBuilderFactory->create()->willReturn($pqb1, $pqb2);

        $pqb1->addFilter('family', Operators::IN_LIST, ['first_family'])->shouldBeCalled();
        $pqb1->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $pqb1->execute()->willReturn($cursor1);

        $cursor1->rewind()->shouldBeCalled();
        $cursor1->valid()->willReturn(true, false);
        $cursor1->next()->willReturn($rootProductModel1);
        $cursor1->current()->willReturn($rootProductModel1);

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel1])->shouldBeCalled();

        $rootProductModel1->hasProductModels()->willReturn(true);
        $rootProductModel1->getProductModels()->willReturn($subProductModelCollection1);
        $subProductModelCollection1->toArray()->willReturn([$subProductModel1]);

        $subProductModel1->hasProductModels()->willReturn(false);
        $subProductModel1->getProducts()->willReturn($productCollection1);
        $productCollection1->isEmpty()->willReturn(false);
        $productCollection1->toArray()->willReturn([$product1]);

        $validator->validate($rootProductModel1)->willReturn($rootProductModelViolationLists1);
        $validator->validate($subProductModel1)->willReturn($subProductModelViolationLists1);
        $validator->validate($product1)->willReturn($productViolationLists1);

        $rootProductModelViolationLists1->count()->willReturn(0);
        $subProductModelViolationLists1->count()->willReturn(0);
        $productViolationLists1->count()->willReturn(0);

        $productModelSaver->saveAll([$rootProductModel1])->shouldBeCalled();
        $productModelSaver->saveAll([$subProductModel1])->shouldBeCalled();
        $productSaver->saveAll([$product1])->shouldBeCalled();

        $familyRepository->findOneByIdentifier('second_family')->willReturn($family2);

        $pqb2->addFilter('family', Operators::IN_LIST, ['second_family'])->shouldBeCalled();
        $pqb2->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $pqb2->execute()->willReturn($cursor2);

        $cursor2->rewind()->shouldBeCalled();
        $cursor2->valid()->willReturn(true, false);
        $cursor2->next()->willReturn($rootProductModel2);
        $cursor2->current()->willReturn($rootProductModel2);

        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel2])->shouldBeCalled();

        $rootProductModel2->hasProductModels()->willReturn(false);
        $rootProductModel2->getProducts()->willReturn($productCollection2);
        $productCollection2->isEmpty()->willReturn(false);
        $productCollection2->toArray()->willReturn([$product2]);

        $validator->validate($rootProductModel2)->willReturn($rootProductModelViolationLists2);
        $validator->validate($product2)->willReturn($productViolationLists2);

        $rootProductModelViolationLists2->count()->willReturn(0);
        $productViolationLists2->count()->willReturn(0);

        $productModelSaver->saveAll([$rootProductModel2])->shouldBeCalled();
        $productSaver->saveAll([$product2])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(5);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $objectDetacher->detach($rootProductModel1)->shouldBeCalled();
        $objectDetacher->detach($subProductModel1)->shouldBeCalled();
        $objectDetacher->detach($product1)->shouldBeCalled();
        $objectDetacher->detach($rootProductModel2)->shouldBeCalled();
        $objectDetacher->detach($product2)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_skips_if_the_family_is_unknown(
        $familyReader,
        $familyRepository,
        $productModelQueryBuilderFactory,
        $productModelSaver,
        $jobRepository,
        StepExecution $stepExecution
    ) {
        $familyReader->read()->willReturn(['code' => 'unkown_family'], null);
        $familyRepository->findOneByIdentifier('unkown_family')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $productModelQueryBuilderFactory->create()->shouldNotBeCalled();
        $productModelSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_handles_invalid_lines(
        $familyReader,
        $familyRepository,
        $productModelQueryBuilderFactory,
        $productModelSaver,
        $jobRepository,
        StepExecution $stepExecution
    ) {
        $familyReader->read()->willThrow(InvalidItemException::class);
        $familyReader->read()->willReturn(null);

        $familyRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $productModelQueryBuilderFactory->create()->shouldNotBeCalled();
        $productModelSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
