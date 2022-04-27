<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Validation;

use Symfony\Component\Validator\Constraint;

final class UniqueContributorEmail extends Constraint
{
    public string $message = 'onboarder.supplier.supplier_edit.contributors_form.notification.contributor_email_already_exists.content';
}
