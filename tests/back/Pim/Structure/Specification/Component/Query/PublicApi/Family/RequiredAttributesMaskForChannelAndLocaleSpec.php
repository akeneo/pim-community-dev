<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use PhpSpec\ObjectBehavior;

final class RequiredAttributesMaskForChannelAndLocaleSpec extends ObjectBehavior
{
    public function it_returns_the_right_product_completeness_for_a_given_local_and_channel()
    {
        $productCompleteness = new CompletenessProductMask(5, "bob", "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);

        $this->beConstructedWith('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US']);
        $this->productCompleteness($productCompleteness)->shouldBeLike(new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']));
    }
}
