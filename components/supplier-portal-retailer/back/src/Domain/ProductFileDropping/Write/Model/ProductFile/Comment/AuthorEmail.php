<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Comment;

use Webmozart\Assert\Assert;

final class AuthorEmail
{
    private function __construct(private string $authorEmail)
    {
        Assert::email($this->authorEmail, 'The author email of the comment is not valid.');
    }

    public static function fromString(string $authorEmail): self
    {
        return new self($authorEmail);
    }

    public function __toString(): string
    {
        return $this->authorEmail;
    }
}
