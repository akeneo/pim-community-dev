<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Builder;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Ramsey\Uuid\Uuid;

final class ProductFileBuilder
{
    private ?string $identifier = null;
    private string $originalFilename = 'file.xlsx';
    private string $path = 'path/to/file.xlsx';
    private string $contributorEmail = 'contributor@example.com';
    private ?string $uploadedBySupplier = null;
    private ?\DateTimeImmutable $uploadedAt = null;

    public function withIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function withOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function withPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function withContributorEmail(string $contributorEmail): self
    {
        $this->contributorEmail = $contributorEmail;

        return $this;
    }

    public function withUploadedBySupplier(string $uploadedBySupplier): self
    {
        $this->uploadedBySupplier = $uploadedBySupplier;

        return $this;
    }

    public function withUploadedAt(\DateTimeImmutable $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function build(): ProductFile
    {
        return ProductFile::create(
            $this->identifier ?? Uuid::uuid4()->toString(),
            $this->originalFilename,
            $this->path,
            $this->contributorEmail,
            new Supplier(
                $this->uploadedBySupplier ?? Uuid::uuid4()->toString(),
                'supplier_code',
                'Supplier label',
            ),
            $this->uploadedAt ?? new \DateTimeImmutable(),
        );
    }
}
