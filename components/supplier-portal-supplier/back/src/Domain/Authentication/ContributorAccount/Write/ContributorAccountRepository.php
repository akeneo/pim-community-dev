<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;

interface ContributorAccountRepository
{
    public function save(ContributorAccount $contributorAccount): void;
    public function find(Identifier $contributorAccountIdentifier): ?ContributorAccount;
    public function findByEmail(Email $email): ?ContributorAccount;
    public function deleteByEmail(string $email): void;
}
