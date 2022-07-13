<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorIdentifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\SupplierIdentifier;

final class SupplierFile
{
    private Identifier $identifier;
    private Filename $filename;
    private Path $path;
    private ?ContributorIdentifier $uploadedByContributor;
    private SupplierIdentifier $uploadedBySupplier;
    private \DateTimeInterface $uploadedAt;
    private ?\DateTimeInterface $downloadedAt;
    private array $events = [];

    private function __construct(
        string $identifier,
        string $filename,
        string $path,
        ?string $uploadedByContributor,
        string $uploadedBySupplier,
        ?\DateTimeInterface $uploadedAt,
        ?\DateTimeInterface $downloadedAt,
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->filename = Filename::fromString($filename);
        $this->path = Path::fromString($path);
        $this->uploadedByContributor = ContributorIdentifier::fromString($uploadedByContributor);
        $this->uploadedBySupplier = SupplierIdentifier::fromString($uploadedBySupplier);
        $this->uploadedAt = $uploadedAt;
        $this->downloadedAt = $downloadedAt;
    }

    public static function create(
        string $identifier,
        string $filename,
        string $path,
        string $uploadedByContributor,
        string $uploadedBySupplier,
    ): self {
        $supplierFile = new self(
            $identifier,
            $filename,
            $path,
            $uploadedByContributor,
            $uploadedBySupplier,
            new \DateTimeImmutable(),
            null,
        );

        $supplierFile->events[] = new SupplierFileAdded($supplierFile);

        return $supplierFile;
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function filename(): string
    {
        return (string) $this->filename;
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

    public function downloadedAt(): ?string
    {
        return $this->downloadedAt?->format('Y-m-d H:i:s');
    }

    public function events(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
