<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate that the locale exists.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleValidator extends ConstraintValidator
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $locale = $this->localeRepository->findOneByIdentifier($value);
        if (null === $locale) {
            if (null !== $constraint->propertyPath) {
                $this->context->setNode(
                    $value,
                    $this->context->getObject(),
                    $this->context->getMetadata(),
                    $constraint->propertyPath
                );
            }

            $this->context->buildViolation(
                $constraint->message,
                ['%locale%' => $value]
            )->addViolation();
        }
    }
}
