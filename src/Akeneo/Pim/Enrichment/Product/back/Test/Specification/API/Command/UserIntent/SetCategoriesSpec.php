<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetCategoriesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['categoryA', 'categoryB']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetCategories::class);
        $this->shouldImplement(CategoryUserIntent::class);

        $this->categoriesCodes()->shouldReturn(['categoryA', 'categoryB']);
    }
}
