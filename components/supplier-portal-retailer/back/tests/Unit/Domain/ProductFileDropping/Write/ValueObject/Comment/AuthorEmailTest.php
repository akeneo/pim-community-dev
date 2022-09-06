<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\ValueObject\Comment;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment\AuthorEmail;
use PHPUnit\Framework\TestCase;

final class AuthorEmailTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateACommentAuthorEmailIfItIsNotValid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException('The author email of the comment is not valid.'),
        );

        AuthorEmail::fromString('a@a');
    }

    /** @test */
    public function itCreatesACommentAuthorEmailIfItIsValid(): void
    {
        $authorEmail = AuthorEmail::fromString('gus@los-pollos-hermanos.io');

        static::assertInstanceOf(AuthorEmail::class, $authorEmail);
        static::assertSame('gus@los-pollos-hermanos.io', (string) $authorEmail);
    }
}
