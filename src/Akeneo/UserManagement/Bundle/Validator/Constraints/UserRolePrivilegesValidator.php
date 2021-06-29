<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRolePrivilegesValidator extends ConstraintValidator
{
    private AclManager $aclManager;

    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UserRolePrivileges::class);
        if (!is_array($value)) {
            return;
        }

        $privilegeIds = [];
        foreach ($this->aclManager->getAllExtensions() as $extension) {
            foreach ($extension->getClasses() as $class) {
                $privilegeIds[] = sprintf('%s:%s', $extension->getExtensionKey(), $class->getClassName());
            }
        }

        $nonExistentPrivileges = \array_diff(\array_keys($value), $privilegeIds);
        if ([] !== $nonExistentPrivileges) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ invalid_permissions }}' => \implode(', ', $nonExistentPrivileges),
                ]
            )->setInvalidValue(\array_keys(\array_filter($value)))->addViolation();
        }
    }
}
