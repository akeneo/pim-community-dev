<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;

use Webmozart\Assert\Assert;

final class Content
{
    private const MAX_CHARACTERS = 255;

    private function __construct(private string $content)
    {
        Assert::minLength(
            $content,
            1,
            'The comment content must not be empty.',
        );
        Assert::maxLength(
            $content,
            self::MAX_CHARACTERS,
            'The comment content must not exceed 255 characters.',
        );
    }

    public static function fromString(string $content): self
    {
        return new self(strip_tags(trim($content)));
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
