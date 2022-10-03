<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;

final class Content
{
    private const MAX_CHARACTERS = 255;

    private function __construct(private string $content)
    {
        if (0 >= \mb_strlen($this->content)) {
            throw new EmptyComment();
        }
        if (self::MAX_CHARACTERS < \mb_strlen($this->content)) {
            throw new CommentTooLong();
        }
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
