<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment\AuthorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment\Content;

final class Comment
{
    private Content $content;
    private AuthorEmail $authorEmail;
    private \DateTimeImmutable $createdAt;
    private bool $isNew;

    private function __construct(string $content, string $authorEmail, \DateTimeImmutable $createdAt, bool $isNew)
    {
        $this->content = Content::fromString($content);
        $this->authorEmail = AuthorEmail::fromString($authorEmail);
        $this->createdAt = $createdAt;
        $this->isNew = $isNew;
    }

    public static function create(string $content, string $authorEmail, \DateTimeImmutable $createdAt): self
    {
        return new self($content, $authorEmail, $createdAt, true);
    }

    public static function hydrate(string $content, string $authorEmail, \DateTimeImmutable $createdAt): self
    {
        return new self($content, $authorEmail, $createdAt, false);
    }

    public function content(): string
    {
        return (string) $this->content;
    }

    public function authorEmail(): string
    {
        return (string) $this->authorEmail;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }
}
