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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function it_gives_its_code()
    {
        $attributeCode = new AttributeCode('name');

        $this->beConstructedWith(
            $attributeCode,
            new AttributeType('pim_catalog_text')
        );

        $this->getCode()->shouldBe($attributeCode);
    }

    function it_gives_its_type()
    {
        $attributeType = new AttributeType('pim_catalog_text');

        $this->beConstructedWith(
            new AttributeCode('name'),
            $attributeType
        );

        $this->getType()->shouldBe($attributeType);
    }
}
