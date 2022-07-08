<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\Model;

use PhpSpec\ObjectBehavior;

class ViolationCodeSpec extends ObjectBehavior
{
    function it_builds_global_violation_code()
    {
        $this->buildGlobalViolationCode(1, 2)->shouldReturn(3);
        $this->buildGlobalViolationCode(1, 2, 4)->shouldReturn(7);
        $this->buildGlobalViolationCode(1, 8)->shouldReturn(9);
    }

    function it_contains_code_into_global_code()
    {
        $this->containsViolationCode(7, 1)->shouldReturn(true);
        $this->containsViolationCode(7, 2)->shouldReturn(true);
        $this->containsViolationCode(7, 4)->shouldReturn(true);
        $this->containsViolationCode(7, 8)->shouldReturn(false);
    }
}
