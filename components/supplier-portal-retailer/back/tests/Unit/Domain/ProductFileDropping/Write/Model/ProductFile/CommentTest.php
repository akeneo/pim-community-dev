<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model\ProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Comment;
use Akeneo\SupplierPortal\Retailer\Test\Unit\Fakes\FrozenClock;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    /** @test */
    public function itCreatesAComment(): void
    {
        $createdAt = (new FrozenClock('2022-09-07 08:54:38'))->now();
        $comment = Comment::create(
            'Your product file is awesome!',
            'jimmy@punchline.com',
            $createdAt,
        );

        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame('Your product file is awesome!', $comment->content());
        static::assertSame('jimmy@punchline.com', $comment->authorEmail());
        static::assertSame($createdAt, $comment->createdAt());
    }
}
