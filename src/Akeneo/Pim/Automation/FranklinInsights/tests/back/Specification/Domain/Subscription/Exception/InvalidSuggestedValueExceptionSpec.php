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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\InvalidSuggestedValueException;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InvalidSuggestedValueExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_suggested_value_exception(): void
    {
        $this->shouldHaveType(InvalidSuggestedValueException::class);
        $this->shouldBeAnInstanceOf(\InvalidArgumentException::class);
    }

    public function it_has_empty_name_message(): void
    {
        $this->beConstructedThrough('emptyAttributeCode');
        $this->getMessage()->shouldReturn('"pimAttributeCode" must not be empty');
    }

    public function it_has_empty_value_message(): void
    {
        $this->beConstructedThrough('emptyValue');
        $this->getMessage()->shouldReturn('"value" must not be empty');
    }

    public function it_has_invalid_value_message(): void
    {
        $this->beConstructedThrough('invalidValue');
        $this->getMessage()->shouldReturn('"value" must be a string or an array of strings');
    }
}
