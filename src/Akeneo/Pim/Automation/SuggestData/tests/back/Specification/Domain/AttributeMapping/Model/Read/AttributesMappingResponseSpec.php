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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingResponseSpec extends ObjectBehavior
{
    public function it_is_an_attributes_mapping_response(): void
    {
        $this->shouldHaveType(AttributesMappingResponse::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_check_if_attribute_exist(): void
    {
        $this->hasPimAttribute(new AttributeCode('color'))->shouldReturn(false);

        $this->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', 1, null));
        $this->hasPimAttribute(new AttributeCode('pim_color'))->shouldReturn(true);
        $this->hasPimAttribute(new AttributeCode('burger'))->shouldReturn(false);
    }
}
