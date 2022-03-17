<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Contributor\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Contributor\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAndFindsAContributor(): void
    {
        $contributorRepository = new InMemoryRepository();

        $contributorRepository->save(
            Contributor\Model\Contributor::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'foo@foo.bar',
                '44ce8069-8da1-4986-872f-311737f46f01',
            )
        );

        $contributor = $contributorRepository->find(
            Contributor\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $contributor->identifier());
        static::assertSame('foo@foo.bar', $contributor->email());
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f01', $contributor->supplierIdentifier());
    }

    /** @test */
    public function itReturnsNullWhenAContributorCannotBeFound(): void
    {
        static::assertNull(
            (new InMemoryRepository())
                ->find(Contributor\ValueObject\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'))
        );
    }
}
