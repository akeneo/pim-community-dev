<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\RefreshProductCompletenessesAndIndex;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshProductCompletenessesAndIndexSpec extends ObjectBehavior
{
    public function let(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $this->beConstructedWith(
            $computeAndPersistProductCompletenesses,
            $productModelDescendantsAndAncestorsIndexer,
            $getDescendantVariantProductIdentifiers
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RefreshProductCompletenessesAndIndex::class);
    }

    function it_computes_variant_products_and_indexes(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $productModelCodes = ['pm1', 'pm2'];
        $getDescendantVariantProductIdentifiers->fromProductModelCodes($productModelCodes)->willReturn(['p1', 'p2']);

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['p1', 'p2'])->shouldBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes)->shouldBeCalled();

        $this->fromProductModelCodes($productModelCodes);
    }

    function it_just_indexes_if_no_variant_products(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $productModelCodes = ['pm1', 'pm2'];
        $getDescendantVariantProductIdentifiers->fromProductModelCodes($productModelCodes)->willReturn([]);

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes)->shouldBeCalled();

        $this->fromProductModelCodes($productModelCodes);
    }

    function it_does_anything_for_empty_product_model_codes(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelCodes([]);
    }
}
