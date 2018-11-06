<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate that the locale is activated.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActivatedLocaleValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($locale, Constraint $constraint)
    {
        if ($locale instanceof LocaleInterface || (is_string($locale) && '' !== $locale)) {
            if (is_string($locale)) {
                $locale = $this->localeRepository->findOneByIdentifier($locale);
                if (null === $locale) { // will be handled by another validator
                    return;
                }
            }

            if ($locale->isActivated()) {
                return;
            }

            if ('' !== $constraint->propertyPath) {
                $this->context->setNode($locale, $this->context->getObject(), $this->context->getMetadata(), $constraint->propertyPath);
            }

            $this->context->buildViolation($constraint->message, ['%locale%' => $locale])->addViolation();
        }
    }
}
