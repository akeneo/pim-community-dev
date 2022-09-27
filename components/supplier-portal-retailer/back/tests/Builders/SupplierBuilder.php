<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Builders;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Ramsey\Uuid\Uuid;

final class SupplierBuilder
{
    private ?string $identifier = null;
    private string $code = 'supplier_code';
    private string $label = 'Supplier label';
    private array $contributors = [];

    public function withIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function withCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function withLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function withContributors(array $contributors): self
    {
        $this->contributors = $contributors;

        return $this;
    }

    public function build(): Supplier
    {
        if (null === $this->identifier) {
            $this->identifier = Uuid::uuid4()->toString();
        }

        return Supplier::create($this->identifier, $this->code, $this->label, $this->contributors);
    }
}
