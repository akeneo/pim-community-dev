<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;

final class ProductFile
{
    private Identifier $identifier;
    private Filename $originalFilename;
    private Path $path;
    private ?ContributorEmail $contributorEmail;
    private string $uploadedBySupplier;
    private \DateTimeInterface $uploadedAt;
    private bool $downloaded;
    private array $events = [];
    private array $newRetailerComments = [];
    private array $newSupplierComments = [];

    private function __construct(
        string $identifier,
        string $originalFilename,
        string $path,
        ?string $contributorEmail,
        string $uploadedBySupplier,
        ?\DateTimeInterface $uploadedAt,
        bool $downloaded = false,
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->originalFilename = Filename::fromString($originalFilename);
        $this->path = Path::fromString($path);
        $this->contributorEmail = ContributorEmail::fromString($contributorEmail);
        $this->uploadedBySupplier = $uploadedBySupplier;
        $this->uploadedAt = $uploadedAt;
        $this->downloaded = $downloaded;
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
    ): self {
        return new self(
            $identifier,
            $originalFilename,
            $path,
            $contributorEmail,
            $uploadedBySupplier,
            new \DateTimeImmutable($uploadedAt),
            $downloaded,
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
        $this->newRetailerComments[] = Comment::create($content, $authorEmail, $createdAt);
    }

    public function addNewSupplierComment(string $content, string $authorEmail, \DateTimeImmutable $createdAt): void
    {
        $this->newSupplierComments[] = Comment::create($content, $authorEmail, $createdAt);
    }

    /**
     * @return Comment[]
     */
    public function newRetailerComments(): array
    {
        return $this->newRetailerComments;
    }

    /**
     * @return Comment[]
     */
    public function newSupplierComments(): array
    {
        return $this->newSupplierComments;
    }
}
