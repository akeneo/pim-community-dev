<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Completeness\MaskItemGenerator;
use PhpSpec\ObjectBehavior;

class MaskItemGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(MaskItemGeneratorForAttributeType::class);
        $this->shouldHaveType(MaskItemGenerator::class);
    }

    function it_supports_table_attributes()
    {
        $this->supportedAttributeTypes()->shouldBe(['pim_catalog_table']);
    }

    function it_builds_a_mask()
    {
        $this->forRawValue('nutrition', 'mobile', 'en_US', [])->shouldBe(['nutrition-mobile-en_US']);
    }
}
