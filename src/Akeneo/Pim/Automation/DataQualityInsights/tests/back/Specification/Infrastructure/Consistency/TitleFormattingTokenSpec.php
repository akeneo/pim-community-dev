<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency;

use PhpSpec\ObjectBehavior;

final class TitleFormattingTokenSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('aJwtSalt', 'http://localhost:42', '/a/path', 'papoProjectCodeTrunca...', 'p4p0Pr0j3ctC0d3H4sh3d');
    }

    public function it_returns_a_jwt_token_as_string()
    {
        $this->getTokenAsString()->shouldReturn('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjdXN0b21lcl9pZHMiOnsiYWtlbmVvX3BpbV91cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6NDIiLCJ2Y3MiOltdLCJwYXBvX3Byb2plY3RfY29kZV90cnVuY2F0ZWQiOiJwYXBvUHJvamVjdENvZGVUcnVuY2EuLi4iLCJwYXBvX3Byb2plY3RfY29kZV9oYXNoZWQiOiJwNHAwUHIwajNjdEMwZDNINHNoM2QifX0.GwNrg4yB5KDt1GAoOZUWKfaWpEK4YnGOP9oOaZDNMGE');
    }
}
