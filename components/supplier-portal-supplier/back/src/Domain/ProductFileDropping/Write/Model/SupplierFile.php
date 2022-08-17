<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;

final class SupplierFile
{
    private Identifier $identifier;
    private Filename $originalFilename;
    private Path $path;
    private ?ContributorEmail $contributorEmail;
    private Supplier $uploadedBySupplier;
    private \DateTimeInterface $uploadedAt;
    private bool $downloaded;
    private array $events = [];

    private function __construct(
        string $identifier,
        string $originalFilename,
        string $path,
        ?string $contributorEmail,
        Supplier $uploadedBySupplier,
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
        $supplierFile = new self(
            $identifier,
            $originalFilename,
            $path,
            $contributorEmail,
            $uploadedBySupplier,
            new \DateTimeImmutable(),
        );

        $supplierFile->events[] = new SupplierFileAdded($supplierFile);

        return $supplierFile;
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

    public function supplierLabel(): string
    {
        return $this->uploadedBySupplier->label;
    }

    public function supplierIdentifier(): string
    {
        return $this->uploadedBySupplier->identifier;
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
}
