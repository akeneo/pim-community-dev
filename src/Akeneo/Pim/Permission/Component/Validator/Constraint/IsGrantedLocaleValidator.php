<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validate that the locale is activated.
 */
class IsGrantedLocaleValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->localeRepository = $localeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($localeCode, Constraint $constraint)
    {
        Assert::string($localeCode, 'Locale code should be a string to validate that it is a granted locale.');
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);
        if (null === $locale || !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            $this->context->buildViolation($constraint->message, ['%locale%' => $localeCode])->addViolation();
        }
    }
}
