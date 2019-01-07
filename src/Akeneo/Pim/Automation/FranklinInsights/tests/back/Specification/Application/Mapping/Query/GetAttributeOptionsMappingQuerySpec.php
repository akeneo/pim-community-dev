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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $this->beConstructedWith($familyCode, $franklinAttributeId);
    }

    public function it_is_a_get_attribute_option_mapping_query(): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $this->beConstructedWith($familyCode, $franklinAttributeId);
        $this->shouldBeAnInstanceOf(GetAttributeOptionsMappingQuery::class);
    }

    public function it_returns_family_code(): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $this->beConstructedWith($familyCode, $franklinAttributeId);

        $this->familyCode()->shouldReturn($familyCode);
    }

    public function it_returns_franklin_attribute_id(): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');

        $this->beConstructedWith($familyCode, $franklinAttributeId);

        $this->franklinAttributeId()->shouldReturn($franklinAttributeId);
    }
}
