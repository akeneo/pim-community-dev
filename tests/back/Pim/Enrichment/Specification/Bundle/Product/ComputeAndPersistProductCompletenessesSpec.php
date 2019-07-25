<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Completeness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeAndPersistProductCompletenessesSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $this->beConstructedWith($productRepository, $completenessCalculator, $saveProductCompletenesses);
    }

    function it_is_a_compute_product_completeness_event_subscriber()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenesses::class);
    }

    function it_computes_and_saves_completenesses_for_a_product(
        ProductRepositoryInterface $productRepository,
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $product = new Product();
        $product->setId(42);
        $ecommerce = new Channel();
        $ecommerce->setCode('ecommerce');
        $frFr = new Locale();
        $frFr->setCode('fr_FR');
        $enUs = new Locale();
        $enUs->setCode('en_US');
        $description = new Attribute();
        $description->setCode('description');
        $picture = new Attribute();
        $picture->setCode('picture');

        $completenessCalculator->calculate($product)->willReturn(
            [
                new Completeness($product, $ecommerce, $enUs, new ArrayCollection([$description]), 1, 20),
                new Completeness($product, $ecommerce, $enUs, new ArrayCollection([$description, $picture]), 2, 25),
            ]
        )->shouldBeCalled();
        $saveProductCompletenesses->save(Argument::type(ProductCompletenessCollection::class))->shouldBeCalled();

        $productRepository->findBy(['identifier' => ['product_1']])->shouldBeCalled()->willReturn([$product]);
        $this->fromProductIdentifier('product_1');
    }
}
