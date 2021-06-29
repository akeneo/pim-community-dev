<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserOwnsDefaultGridViewsValidator extends ConstraintValidator
{
    public function validate($user, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UserOwnsDefaultGridViews::class);
        if (!$user instanceof UserInterface) {
            return;
        }

        foreach ($user->getDefaultGridViews() as $defaultGridView) {
            if (!$defaultGridView->isPublic() && $defaultGridView->getOwner() !== $user) {
                $path = \sprintf('default_%s_view', \str_replace('-', '_', $defaultGridView->getDatagridAlias()));
                $this->context->buildViolation(
                    $constraint->message,
                    [
                        '{{ label }}' => $defaultGridView->getLabel(),
                        '{{ username }}' => $user->getUsername(),
                    ]
                )->atPath($path)->setInvalidValue($defaultGridView->getLabel())->addViolation();
            }
        }
    }
}
