<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

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
