<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Validation;

use Symfony\Component\Validator\Constraint;

final class UniqueContributorEmail extends Constraint
{
    public string $message = 'onboarder.supplier.contributor.email_already_exists';
}
