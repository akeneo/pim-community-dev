<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductsCompletenessCollectionsWereChangedSpec extends ObjectBehavior
{
    public function it_cant_be_created_empty()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_created_with_invalid_products_completeness_collection()
    {
        $this->beConstructedWith(['completeness1','completeness2']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
