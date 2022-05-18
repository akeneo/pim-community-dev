<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Authentication\ContributorAccount\Write\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /** @test */
    public function itCanGenerateAnAccessToken(): void
    {
        $accessToken = AccessToken::generate();
        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertGreaterThan(0, (string) $accessToken);
    }
}
