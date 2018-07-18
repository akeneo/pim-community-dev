<?php

declare(strict_types=1);

namespace spec\Pim\Component\Connector\Job;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Connector\Job\ComputeDataRelatedToFamilyVariantsTasklet;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ComputeDataRelatedToFamilyProductsTaskletSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        CacheClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $productQueryBuilderFactory,
            $familyReader,
            $productSaver,
            $objectDetacher,
            $cacheClearer,
            $jobRepository,
            10
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeDataRelatedToFamilyVariantsTasklet::class);
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
        StepExecution $stepExecution,
        ProductAndProductModelQueryBuilder $pqb,
        CursorInterface $cursor,
        ConstraintViolationListInterface $productViolationLists
    ) {
        $familyReader->read()->willReturn(['code' => 'my_family'], null);
        $familyRepository->findOneByIdentifier('my_family')->willReturn($family);

        $family->getCode()->willReturn('family_code');

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($product1, $product2);
        $cursor->next()->shouldBeCalled();

        $productViolationLists->count()->willReturn(0);

        $productSaver->saveAll([$product1, $product2])->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(1);

        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

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
