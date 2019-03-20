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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyQuerySpec extends ObjectBehavior
{
    public function it_is_a_get_attributes_mapping_query(): void
    {
        $this->beConstructedWith(new FamilyCode('camcorders'));
        $this->shouldHaveType(GetAttributesMappingByFamilyQuery::class);
    }

    public function it_returns_the_family_code(): void
    {
        $familyCode = new FamilyCode('camcorders');
        $this->beConstructedWith($familyCode);

        $this->getFamilyCode()->shouldReturn($familyCode);
    }
}
