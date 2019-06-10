<?php

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the user preferences constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesValidator extends ConstraintValidator
{
    /**
     * Validate the user preferences
     *
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
     * @param Constraint                              $constraint
     */
    public function validate($user, Constraint $constraint)
    {
        $this->validateCatalogLocale($user, $constraint);
        $this->validateCatalogScope($user, $constraint);
        $this->validateDefaultTree($user, $constraint);
    }

    /**
     * Validate catalog locale
     *
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
     * @param Constraint                              $constraint
     */
    protected function validateCatalogLocale($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getCatalogLocale'])) {
            $locale = $user->getCatalogLocale();
            if (!$locale) {
                $this->context->buildViolation($constraint->missingLocaleMsg)
                    ->addViolation();
            } elseif (!$locale->isActivated()) {
                $this->context->buildViolation($constraint->inactiveLocaleMsg)
                    ->addViolation();
            }
        }
    }

    /**
     * Validate catalog Scope
     *
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
     * @param Constraint                              $constraint
     */
    protected function validateCatalogScope($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getCatalogScope'])) {
            if (!$user->getCatalogScope()) {
                $this->context->buildViolation($constraint->missingScopeMsg)
                    ->addViolation();
            }
        }
    }

    /**
     * Validate default tree
     *
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
     * @param Constraint                              $constraint
     */
    protected function validateDefaultTree($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getDefaultTree'])) {
            $tree = $user->getDefaultTree();
            if (!$tree) {
                $this->context->buildViolation($constraint->missingTreeMsg)
                    ->addViolation();
            } elseif (!$tree->isRoot()) {
                $this->context->buildViolation($constraint->invalidTreeMsg)
                    ->addViolation();
            }
        }
    }
}
