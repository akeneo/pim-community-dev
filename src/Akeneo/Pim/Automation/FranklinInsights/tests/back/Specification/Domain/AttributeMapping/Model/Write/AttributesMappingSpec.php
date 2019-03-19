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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new FamilyCode('watches'));
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AttributesMapping::class);
    }

    public function it_returns_the_family_code(): void
    {
        $familyCode = new FamilyCode('watches');
        $this->beConstructedWith($familyCode);

        $this->familyCode()->shouldReturn($familyCode);
    }

    public function it_maps_a_franklin_attribute_to_a_pim_attribute(): void
    {
        $this->map('franklin_attr', 'text', AttributeBuilder::fromCode('code'))->shouldReturn(null);
        $this->mapping()->shouldHaveCount(1);
        $this->mapping()[0]->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_throws_an_exception_when_it_cannot_map_the_attribute(): void
    {
        $attribute = (new AttributeBuilder())->isLocalizable()->build();

        $this
            ->shouldThrow(AttributeMappingException::localizableAttributeNotAllowed())
            ->during('map', ['franklin_attr', 'text', $attribute]);
    }
}
