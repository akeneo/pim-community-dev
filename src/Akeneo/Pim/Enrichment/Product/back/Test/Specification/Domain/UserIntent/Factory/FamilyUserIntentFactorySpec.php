<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUserIntentFactorySpec extends ObjectBehavior
{
    function it_returns_a_set_family()
    {
        $this->create('family', 'accessories')->shouldBeLike([new SetFamily('accessories')]);
    }

    function it_returns_a_remove_family()
    {
        $this->create('family', null)->shouldBeLike([new RemoveFamily()]);
        $this->create('family', '')->shouldBeLike([new RemoveFamily()]);
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['family', 12]);
    }
}
