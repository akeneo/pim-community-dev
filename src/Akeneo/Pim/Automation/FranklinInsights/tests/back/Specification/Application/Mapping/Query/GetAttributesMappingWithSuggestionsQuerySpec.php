<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsQuerySpec extends ObjectBehavior
{
    public function it_is_a_get_attributes_mapping_with_suggestion_query(): void
    {
        $this->beConstructedWith(new FamilyCode('camcorders'));
        $this->shouldHaveType(GetAttributesMappingWithSuggestionsQuery::class);
    }

    public function it_returns_the_family_code(): void
    {
        $familyCode = new FamilyCode('camcorders');
        $this->beConstructedWith($familyCode);

        $this->getFamilyCode()->shouldReturn($familyCode);
    }
}
