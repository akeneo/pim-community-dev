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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    public function it_is_an_attribute_write_model()
    {
        $this->beConstructedWith(
            new AttributeCode('width'),
            new AttributeLabel('Width'),
            new AttributeType('pim_catalog_number')
        );

        $this->shouldHaveType(Attribute::class);
    }

    public function it_gets_the_attribute_code()
    {
        $attributeCode = new AttributeCode('width');

        $this->beConstructedWith(
            $attributeCode,
            new AttributeLabel('Width'),
            new AttributeType('pim_catalog_number')
        );

        $this->getCode()->shouldReturn($attributeCode);
    }

    public function it_gets_the_attribute_label()
    {
        $attributeLabel = new AttributeLabel('Width');

        $this->beConstructedWith(
            new AttributeCode('width'),
            $attributeLabel,
            new AttributeType('pim_catalog_number')
        );

        $this->getLabel()->shouldReturn($attributeLabel);
    }

    public function it_gets_the_attribute_type()
    {
        $attributeType = new AttributeType('pim_catalog_number');

        $this->beConstructedWith(
            new AttributeCode('width'),
            new AttributeLabel('Width'),
            $attributeType
        );

        $this->getType()->shouldReturn($attributeType);
    }
}
