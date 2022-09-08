<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\ValueObject\Comment;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment\Content;
use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateACommentContentIfItExceedsTwoHundredFiftyFiveCharacters(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException('The comment content must not exceed 255 characters.'),
        );

        Content::fromString(str_repeat('q', 256));
    }

    /** @test */
    public function itDoesNotCreateACommentContentIfItIsEmpty(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException('The comment content must not be empty.'),
        );

        Content::fromString('');
    }

    /** @test */
    public function itDoesNotCreateACommentContentIfItContainsSpacesOnly(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException('The comment content must not be empty.'),
        );

        Content::fromString('   ');
    }

    /** @test */
    public function itEscapesHtmlSpecialCharacters(): void
    {
        $content = Content::fromString('Your product file is <strong>awesome</strong>!');

        static::assertInstanceOf(Content::class, $content);
        static::assertSame('Your product file is awesome!', (string) $content);
    }

    /** @test */
    public function itCreatesAndGetsACommentContentIfItsValid(): void
    {
        $content = Content::fromString('Your product file is awesome!');

        static::assertInstanceOf(Content::class, $content);
        static::assertSame('Your product file is awesome!', (string) $content);
    }
}
