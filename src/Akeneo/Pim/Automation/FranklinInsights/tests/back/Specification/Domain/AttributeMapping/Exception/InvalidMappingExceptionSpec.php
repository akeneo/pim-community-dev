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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidMappingExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_mapping_exception(): void
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(InvalidMappingException::class);
    }

    public function it_is_an_exception(): void
    {
        $this->beConstructedWith('className');

        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_returns_the_name_of_the_class_the_exception_was_thrown_from(): void
    {
        $this->beConstructedWith('className');

        $this->getClassName()->shouldReturn('className');
    }
}
