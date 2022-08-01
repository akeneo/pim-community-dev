<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\SupplierIdentifier;

final class SupplierFile
{
    private Identifier $identifier;
    private Filename $originalFilename;
    private Path $path;
    private ?ContributorEmail $uploadedByContributor;
    private SupplierIdentifier $uploadedBySupplier;
    private \DateTimeInterface $uploadedAt;
    private bool $downloaded;
    private array $events = [];

    private function __construct(
        Identifier $identifier,
        string $originalFilename,
        string $path,
        ?string $uploadedByContributor,
        string $uploadedBySupplier,
        ?\DateTimeInterface $uploadedAt,
        bool $downloaded = false,
    ) {
        $this->identifier = $identifier;
        $this->originalFilename = Filename::fromString($originalFilename);
        $this->path = Path::fromString($path);
        $this->uploadedByContributor = ContributorEmail::fromString($uploadedByContributor);
        $this->uploadedBySupplier = SupplierIdentifier::fromString($uploadedBySupplier);
        $this->uploadedAt = $uploadedAt;
        $this->downloaded = $downloaded;
    }

    public static function create(
        string $originalFilename,
        string $path,
        string $uploadedByContributor,
        string $uploadedBySupplier,
    ): self {
        $supplierFile = new self(
            Identifier::generate(),
            $originalFilename,
            $path,
            $uploadedByContributor,
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

    public function uploadedByContributor(): ?string
    {
        return null === $this->uploadedByContributor ? null : (string) $this->uploadedByContributor;
    }

    public function uploadedBySupplier(): string
    {
        return (string) $this->uploadedBySupplier;
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
