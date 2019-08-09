<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/** TODO REMOVE THAT */
class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->beConstructedWith($pqbFactory, $getProductCompletenesses);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessGenerator::class);
    }
}
