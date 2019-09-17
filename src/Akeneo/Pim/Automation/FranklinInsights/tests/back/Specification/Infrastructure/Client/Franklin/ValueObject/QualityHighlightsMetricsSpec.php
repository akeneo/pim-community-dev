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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use PhpSpec\ObjectBehavior;

class QualityHighlightsMetricsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'added' => 10,
            'not_mapped' => 1,
            'value_not_validated' => 2,
            'value_added' => 4,
            'value_mismatch' => 3,
            'value_validated' => 5
        ]);
    }

    public function it_gives_the_number_of_value_suggested()
    {
        $this->getValueSuggested()->shouldReturn(4);
    }

    public function it_gives_the_number_of_name_and_value_suggested()
    {
        $this->getNameAndValueSuggested()->shouldReturn(10);
    }

    public function it_gives_the_number_of_value_in_error()
    {
        $this->getValueInError()->shouldReturn(3);
    }

    public function it_gives_the_number_of_value_validated()
    {
        $this->getValueValidated()->shouldReturn(5);
    }
}
