<?php

namespace Pim\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

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
     * @param User       $user
     * @param Constraint $constraint
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
     * @param User       $user
     * @param Constraint $constraint
     */
    protected function validateCatalogLocale($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getCatalogLocale'])) {
            $locale = $user->getCatalogLocale();
            if (!$locale) {
                $this->context->addViolation($constraint->missingLocaleMessage);
            } elseif (!$locale->isActivated()) {
                $this->context->addViolation($constraint->inactiveLocaleMessage);
            }
        }
    }

    /**
     * Validate catalog Scope
     *
     * @param User       $user
     * @param Constraint $constraint
     */
    protected function validateCatalogScope($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getCatalogScope'])) {
            if (!$user->getCatalogScope()) {
                $this->context->addViolation($constraint->missingScopeMessage);
            }
        }
    }

    /**
     * Validate default tree
     *
     * @param User       $user
     * @param Constraint $constraint
     */
    protected function validateDefaultTree($user, Constraint $constraint)
    {
        if (is_callable([$user, 'getDefaultTree'])) {
            $tree = $user->getDefaultTree();
            if (!$tree) {
                $this->context->addViolation($constraint->missingTreeMessage);
            } elseif (!$tree->isRoot()) {
                $this->context->addViolation($constraint->invalidTreeMessage);
            }
        }
    }
}
