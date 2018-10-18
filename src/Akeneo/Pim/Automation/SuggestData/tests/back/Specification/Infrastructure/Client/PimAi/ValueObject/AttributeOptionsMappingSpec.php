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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeOptionsMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_an_attribute_options_mapping(): void
    {
        $this->shouldHaveType(AttributeOptionsMapping::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldImplement(\Traversable::class);
    }

    public function it_returns_an_iterator(): void
    {
        $this->beConstructedWith([]);
        $this->getIterator()->shouldReturnAnInstanceOf(\ArrayIterator::class);
    }

    public function it_returns_an_iterator_containing_attribute_options_mapping(): void
    {
    }
}
