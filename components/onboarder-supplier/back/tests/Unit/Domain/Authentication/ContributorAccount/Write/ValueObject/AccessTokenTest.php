<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Write\ValueObject;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /** @test */
    public function itCanGenerateAnAccessToken(): void
    {
        $accessToken = AccessToken::generate();
        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertNotEmpty((string) $accessToken);
    }

    /** @test */
    public function itCanBeCreatedFromAString(): void
    {
        $accessToken = AccessToken::fromString('an-access-token');
        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertSame('an-access-token', (string) $accessToken);
    }
}
