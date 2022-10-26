<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFile;
use PHPUnit\Framework\TestCase;

final class CommentProductFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheDataNeededToCreateTheComment(): void
    {
        $commentProductFileReflectionClass = new \ReflectionClass(CommentProductFile::class);
        $properties = $commentProductFileReflectionClass->getProperties();

        $createdAt = new \DateTimeImmutable();
        $sut = new CommentProductFile(
            '9c89942b-4be9-463b-90d8-69c9f000500c',
            'julia@roberts.com',
            'Your product file is awesome!',
            $createdAt,
        );

        static::assertCount(4, $properties);
        static::assertSame('9c89942b-4be9-463b-90d8-69c9f000500c', $sut->productFileIdentifier);
        static::assertSame('julia@roberts.com', $sut->authorEmail);
        static::assertSame('Your product file is awesome!', $sut->content);
        static::assertSame($createdAt, $sut->createdAt);
    }
}
