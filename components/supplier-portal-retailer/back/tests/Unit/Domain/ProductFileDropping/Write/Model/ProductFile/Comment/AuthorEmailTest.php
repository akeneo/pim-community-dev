<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model\ProductFile\Comment;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Comment\AuthorEmail;
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
        $authorEmail = AuthorEmail::fromString('jimmy@punchline.com');

        static::assertInstanceOf(AuthorEmail::class, $authorEmail);
        static::assertSame('jimmy@punchline.com', (string) $authorEmail);
    }
}
