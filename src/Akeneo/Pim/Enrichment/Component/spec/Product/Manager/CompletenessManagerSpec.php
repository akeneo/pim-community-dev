<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Manager;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface;

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
