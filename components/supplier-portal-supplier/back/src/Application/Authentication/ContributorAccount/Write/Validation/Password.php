<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Validation;

use Symfony\Component\Validator\Constraint;

final class Password extends Constraint
{
    public const PASSWORD_MIN_LENGTH_MESSAGE = 'supplier_portal.supplier.contributor_account.validation.min_password_length';
    public const PASSWORD_MAX_LENGTH_MESSAGE = 'supplier_portal.supplier.contributor_account.validation.max_password_length';
    public const PASSWORD_UPPERCASE_LETTER_MESSAGE = 'supplier_portal.supplier.contributor_account.validation.must_contain_an_uppercase_letter';
    public const PASSWORD_LOWERCASE_LETTER_MESSAGE = 'supplier_portal.supplier.contributor_account.validation.must_contain_a_lowercase_letter';
    public const PASSWORD_DIGIT_MESSAGE = 'supplier_portal.supplier.contributor_account.validation.must_contain_a_digit';
}
