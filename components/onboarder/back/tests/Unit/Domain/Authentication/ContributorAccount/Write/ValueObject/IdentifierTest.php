<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Authentication\ContributorAccount\Write\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        Identifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAnIdentifierIfItsValid(): void
    {
        $uuid = Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Identifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
