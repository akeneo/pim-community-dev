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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class FranklinAttributeCreatedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            new AttributeCode('Attr_code'),
            new AttributeType('pim_catalog_text')
        );
    }

    public function it_is_an_event(): void
    {
        $this->shouldHaveType(FranklinAttributeCreated::class);
    }

    public function it_is_a_an_event(): void
    {
        $this->shouldImplement(Event::class);
    }

    public function it_returns_attribute_code(): void
    {
        $attrCode = new AttributeCode('title');
        $attrType = new AttributeType('pim_catalog_text');

        $this->beConstructedWith($attrCode, $attrType);

        $this->getAttributeCode()->shouldReturn($attrCode);
    }

    public function it_returns_attribute_type(): void
    {
        $attrCode = new AttributeCode('title');
        $attrType = new AttributeType('pim_catalog_text');

        $this->beConstructedWith($attrCode, $attrType);

        $this->getAttributeType()->shouldReturn($attrType);
    }
}
