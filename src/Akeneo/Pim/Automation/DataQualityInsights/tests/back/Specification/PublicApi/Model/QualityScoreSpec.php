<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreSpec extends ObjectBehavior
{
    public function it_can_be_constructed_and_returns_letter_and_rate()
    {
        $letter = 'A';
        $rate = 95;

        $this->beConstructedWith($letter, $rate);
        $this->getLetter()->shouldReturn($letter);
        $this->getRate()->shouldReturn($rate);
    }
}
