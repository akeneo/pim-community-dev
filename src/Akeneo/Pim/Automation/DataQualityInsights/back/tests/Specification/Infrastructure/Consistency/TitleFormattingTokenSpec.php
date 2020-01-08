<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency;

use PhpSpec\ObjectBehavior;

final class TitleFormattingTokenSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('aJwtSalt', 'http://localhost:42', '/a/path');
    }

    public function it_returns_a_jwt_token_as_string()
    {
        $this->getTokenAsString()->shouldReturn('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjdXN0b21lcl9pZHMiOnsiYWtlbmVvX3BpbV91cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6NDIiLCJ2Y3MiOltdfX0.1JTh1yeBrZbygJkkLC4rB0g0iSnDo4G385zmZGfPQsA');
    }
}
