<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearerRegistrySpec extends ObjectBehavior
{
    function let(
        ClearerInterface $clearer1,
        ClearerInterface $clearer2
    ) {
        $this->beConstructedWith([
            $clearer1,
            $clearer2,
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ClearerRegistry::class);
    }

    function it_is_a_clearer_registry()
    {
        $this->shouldImplement(ClearerRegistryInterface::class);
    }

    function it_returns_a_clearer(ClearerInterface $clearer1, ClearerInterface $clearer2)
    {
        $clearer1->supportsProperty('title')->willReturn(false);
        $clearer2->supportsProperty('title')->willReturn(true);

        $this->getClearer('title')->shouldReturn($clearer2);
    }

    function it_returns_null_when_no_clearer_is_found(ClearerInterface $clearer1, ClearerInterface $clearer2)
    {
        $clearer1->supportsProperty('categories')->willReturn(false);
        $clearer2->supportsProperty('categories')->willReturn(false);

        $this->getClearer('categories')->shouldReturn(null);
    }
}
