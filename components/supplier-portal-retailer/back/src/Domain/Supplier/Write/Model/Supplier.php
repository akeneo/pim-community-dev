<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Code;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Contributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Label;

final class Supplier
{
    private Identifier $identifier;
    private Code $code;
    private Label $label;
    private Contributors $contributors;
    private array $events = [];

    private function __construct(
        string $identifier,
        string $code,
        string $label,
        array $contributorEmails = [],
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->code = Code::fromString($code);
        $this->label = Label::fromString($label);
        $this->contributors = Contributors::fromEmails($contributorEmails);
    }

    public static function create(string $identifier, string $code, string $label, array $contributorEmails): self
    {
        return new self(
            $identifier,
            $code,
            $label,
            $contributorEmails,
        );
    }

    public function update(string $label, array $contributorEmails): void
    {
        $this->label = Label::fromString($label);

        $deletedContributorEmails = $this->contributors->computeDeletedContributorEmails($contributorEmails);

        $this->events = array_merge(
            $this->events,
            array_map(
                fn (string $deletedContributorEmail) => new ContributorDeleted(
                    Identifier::fromString($this->identifier()),
                    $deletedContributorEmail,
                ),
                $deletedContributorEmails,
            ),
        );

        $newContributorEmails = $this->contributors->computeCreatedContributorEmails($contributorEmails);

        $this->events = array_merge(
            $this->events,
            array_map(
                fn (string $createdContributorEmail) => new ContributorAdded(
                    Identifier::fromString($this->identifier()),
                    $createdContributorEmail,
                    $this->code(),
                ),
                $newContributorEmails,
            ),
        );

        $this->contributors = Contributors::fromEmails($contributorEmails);
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function code(): string
    {
        return (string) $this->code;
    }

    public function label(): string
    {
        return (string) $this->label;
    }

    public function contributors(): array
    {
        return $this->contributors->toArray();
    }

    /**
     * @return object[]
     */
    public function events(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
