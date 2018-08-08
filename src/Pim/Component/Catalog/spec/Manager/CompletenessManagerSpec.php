<?php

namespace spec\Pim\Component\Catalog\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class CompletenessManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CompletenessGeneratorInterface $generator,
        CompletenessRemoverInterface $remover,
        ValueCompleteCheckerInterface $valueCompleteChecker
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $channelRepository,
            $localeRepository,
            $generator,
            $remover,
            $valueCompleteChecker
        );
    }

    function it_bulk_schedules_completeness_on_several_products(
        CompletenessRemoverInterface $remover,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $remover->removeForProductWithoutIndexing($product1)->shouldBeCalled();
        $remover->removeForProductWithoutIndexing($product2)->shouldBeCalled();

        $this->bulkSchedule([$product1, $product2]);
    }
}
