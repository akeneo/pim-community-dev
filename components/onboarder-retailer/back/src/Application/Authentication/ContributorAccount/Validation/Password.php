<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Validation;

use Symfony\Component\Validator\Constraint;

final class Password extends Constraint
{
    public string $passwordMinLengthMessage = 'onboarder.supplier.contributor_account.validation.min_password_length';
    public string $passwordMaxLengthMessage = 'onboarder.supplier.contributor_account.validation.max_password_length';
    public string $passwordUppercaseLetterMessage = 'onboarder.supplier.contributor_account.validation.should_contain_an_uppercase_letter';
    public string $passwordLowercaseLetterMessage = 'onboarder.supplier.contributor_account.validation.should_contain_a_lowercase_letter';
    public string $passwordDigitMessage = 'onboarder.supplier.contributor_account.validation.should_contain_a_digit';
}
