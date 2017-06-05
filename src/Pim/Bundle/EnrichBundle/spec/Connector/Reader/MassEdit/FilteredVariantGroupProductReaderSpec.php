<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FilteredVariantGroupProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $completenessManager,
            $metricConverter,
            true,
            $paginatorFactory,
            $objectDetacher,
            $groupRepository,
            $productRepository,
            $translator
        );
        $this->setStepExecution($stepExecution);
    }

    function it_returns_no_products_when_they_all_are_duplicated(
        $productRepository,
        $paginatorFactory,
        $stepExecution,
        $pqbFactory,
        ProductQueryBuilder $pqb,
        ProductQueryBuilder $pqb2,
        Cursor $cursor,
        Cursor $cursor2,
        JobParameters $jobParameters,
        IdentifiableObjectRepositoryInterface $groupRepository,
        GroupInterface $variantGroup,
        AttributeInterface $axe,
        PaginatorInterface $paginator,
        CursorInterface $eligibleProductsToVariant
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => [12, 13]
                ],
            ],
            'actions' => [
                'value' => 'variantGroupCode',
            ]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $groupRepository->findOneByIdentifier('variantGroupCode')->willReturn($variantGroup);
        $variantGroup->getAxisAttributes()->willReturn([$axe]);
        $variantGroup->getId()->willReturn(42);
        $axe->getCode()->willReturn('axe');

        $productRepository->getEligibleProductsForVariantGroup(42)->willReturn($eligibleProductsToVariant);

        $pqbFactory->create(['filters' => $configuration['filters']])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);

        $pqbFactory->create(['filters' => [['field' => 'id', 'operator' => 'IN', 'value' => ['']]]])->willReturn($pqb2);
        $pqb2->execute()->willReturn($cursor2);

        $this->initialize();
        $this->read()->shouldReturn(null);
    }
}
