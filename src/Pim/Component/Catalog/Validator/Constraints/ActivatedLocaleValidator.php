<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
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
        if (!$locale instanceof LocaleInterface) {
            return;
        }

        if (!$locale->isActivated()) {
            $this->context->buildViolation(
                $constraint->message,
                ['%locale%' => $locale]
            )->addViolation();
        }
    }
}
