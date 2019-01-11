<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('camcorders');
    }

    public function it_is_a_get_attributes_mapping_query(): void
    {
        $this->shouldHaveType(GetAttributesMappingByFamilyQuery::class);
    }

    public function it_returns_the_family_code(): void
    {
        $this->getFamilyCode()->shouldReturn('camcorders');
    }

    public function it_throws_an_exception_if_family_code_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
