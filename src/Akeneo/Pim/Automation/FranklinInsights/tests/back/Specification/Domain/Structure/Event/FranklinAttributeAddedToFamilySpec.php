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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class FranklinAttributeAddedToFamilySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            new AttributeCode('Attr_code'),
            new FamilyCode('Family_code')
        );
    }

    public function it_is_an_event(): void
    {
        $this->shouldHaveType(FranklinAttributeAddedToFamily::class);
    }

    public function it_returns_attribute_code(): void
    {
        $attrCode = new AttributeCode('title');
        $familyCode = new FamilyCode('Family_code');

        $this->beConstructedWith($attrCode, $familyCode);

        $this->getAttributeCode()->shouldReturn($attrCode);
    }

    public function it_returns_family_code(): void
    {
        $attrCode = new AttributeCode('title');
        $familyCode = new FamilyCode('Family_code');

        $this->beConstructedWith($attrCode, $familyCode);

        $this->getFamilyCode()->shouldReturn($familyCode);
    }
}
