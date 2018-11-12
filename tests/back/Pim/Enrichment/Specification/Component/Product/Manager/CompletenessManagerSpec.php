<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManagerSpec extends ObjectBehavior
{
    function let(CompletenessGeneratorInterface $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_bulk_schedules_completeness_on_several_products($generator)
    {
        $product = new Product();

        $generator->generateMissingForProduct($product)->shouldBeCalled();

        $this->generateMissingForProduct($product);
    }
}
