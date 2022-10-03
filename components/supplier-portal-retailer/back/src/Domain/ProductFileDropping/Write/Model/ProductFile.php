<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;

final class ProductFile
{
    const MAX_COMMENTS_PER_PRODUCT_FILE = 50;

    private Identifier $identifier;
    private Filename $originalFilename;
    private Path $path;
    private ?ContributorEmail $contributorEmail;
    private string $uploadedBySupplier;
    private \DateTimeInterface $uploadedAt;
    private bool $downloaded;
    private array $events = [];
    private array $retailerComments = [];
    private array $supplierComments = [];

    private function __construct(
        string $identifier,
        string $originalFilename,
        string $path,
        ?string $contributorEmail,
        string $uploadedBySupplier,
        ?\DateTimeInterface $uploadedAt,
        bool $downloaded = false,
        array $retailerComments = [],
        array $supplierComments = [],
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->originalFilename = Filename::fromString($originalFilename);
        $this->path = Path::fromString($path);
        $this->contributorEmail = ContributorEmail::fromString($contributorEmail);
        $this->uploadedBySupplier = $uploadedBySupplier;
        $this->uploadedAt = $uploadedAt;
        $this->downloaded = $downloaded;
        $this->retailerComments = $retailerComments;
        $this->supplierComments = $supplierComments;
    }

    public static function create(
        string $identifier,
        string $originalFilename,
        string $path,
        string $contributorEmail,
        Supplier $uploadedBySupplier,
    ): self {
        $productFile = new self(
            $identifier,
            $originalFilename,
            $path,
            $contributorEmail,
            $uploadedBySupplier->identifier,
            new \DateTimeImmutable(),
        );

        $productFile->events[] = new ProductFileAdded($productFile, $uploadedBySupplier->label);

        return $productFile;
    }

    public static function hydrate(
        string $identifier,
        string $originalFilename,
        string $path,
        string $contributorEmail,
        string $uploadedBySupplier,
        string $uploadedAt,
        bool $downloaded,
        array $retailerComments = [],
        array $supplierComments = [],
    ): self {
        return new self(
            $identifier,
            $originalFilename,
            $path,
            $contributorEmail,
            $uploadedBySupplier,
            new \DateTimeImmutable($uploadedAt),
            $downloaded,
            $retailerComments,
            $supplierComments,
        );
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function originalFilename(): string
    {
        return (string) $this->originalFilename;
    }

    public function path(): string
    {
        return (string) $this->path;
    }

    public function contributorEmail(): ?string
    {
        return null === $this->contributorEmail ? null : (string) $this->contributorEmail;
    }

    public function uploadedBySupplier(): string
    {
        return $this->uploadedBySupplier;
    }

    public function uploadedAt(): string
    {
        return $this->uploadedAt->format('Y-m-d H:i:s');
    }

    public function downloaded(): bool
    {
        return $this->downloaded;
    }

    public function events(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    public function addNewRetailerComment(string $content, string $authorEmail, \DateTimeImmutable $createdAt): void
    {
        $this->checkCommentsLimitReached();

        $this->retailerComments[] = Comment::create($content, $authorEmail, $createdAt);
    }

    public function addNewSupplierComment(string $content, string $authorEmail, \DateTimeImmutable $createdAt): void
    {
        $this->checkCommentsLimitReached();

        $this->supplierComments[] = Comment::create($content, $authorEmail, $createdAt);
    }

    public function retailerComments(): array
    {
        return $this->retailerComments;
    }

    public function supplierComments(): array
    {
        return $this->supplierComments;
    }

    /**
     * @return Comment[]
     */
    public function newRetailerComments(): array
    {
        return array_filter($this->retailerComments, fn (Comment $comment) => $comment->isNew());
    }

    /**
     * @return Comment[]
     */
    public function newSupplierComments(): array
    {
        return array_filter($this->supplierComments, fn (Comment $comment) => $comment->isNew());
    }

    private function checkCommentsLimitReached(): void
    {
        if (self::MAX_COMMENTS_PER_PRODUCT_FILE <= count($this->retailerComments + $this->supplierComments)) {
            throw new MaxCommentPerProductFileReached();
        }
    }
}
