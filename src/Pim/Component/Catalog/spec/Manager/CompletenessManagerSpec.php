<?php

namespace spec\Pim\Component\Catalog\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
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
        CompletenessGenerator $generator,
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

    function it_generates_missing_completenesses_for_products(
        ChannelInterface $channel,
        $generator
    )
    {
        $generator->generateMissingForProducts($channel, [])->shouldBeCalled();

        $this->generateMissingForProducts($channel, []);
    }
}

// @TODO @merge PIM-7348 - Remove when merged
class CompletenessGenerator implements CompletenessGeneratorInterface {
    public function generateMissingForProduct(ProductInterface $product) {}
    public function generateMissingForChannel(ChannelInterface $channel) {}
    public function generateMissing() {}
    public function generateMissingForProducts(ChannelInterface $channel, array $filters) {}
}
