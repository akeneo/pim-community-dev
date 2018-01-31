<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for localizable product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableValueValidator extends ConstraintValidator
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
     * @param object     $productValue
     * @param Constraint $constraint
     */
    public function validate($productValue, Constraint $constraint)
    {
        /** @var ValueInterface */
        if ($productValue instanceof ValueInterface) {
            $isLocalizable = $productValue->getAttribute()->isLocalizable();
            $localeCode = $productValue->getLocale();

            if ($isLocalizable && null === $localeCode) {
                $this->addExpectedLocaleViolation($constraint, $productValue);
            } elseif ($isLocalizable && !$this->doesLocaleExist($localeCode)) {
                $this->addUnexistingLocaleViolation($constraint, $productValue, $localeCode);
            } elseif (!$isLocalizable && null !== $localeCode) {
                $this->addUnexpectedLocaleViolation($constraint, $productValue);
            }
        }
    }

    /**
     * @param string $localeCode
     *
     * @return bool
     */
    protected function doesLocaleExist($localeCode)
    {
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        return null !== $locale;
    }

    /**
     * @param LocalizableValue $constraint
     * @param ValueInterface   $value
     */
    protected function addExpectedLocaleViolation(LocalizableValue $constraint, ValueInterface $value)
    {
        $this->context->buildViolation(
            $constraint->expectedLocaleMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode()
            ]
        )->addViolation();
    }

    /**
     * @param LocalizableValue $constraint
     * @param ValueInterface   $value
     * @param string           $localeCode
     */
    protected function addUnexistingLocaleViolation(
        LocalizableValue $constraint,
        ValueInterface $value,
        $localeCode
    ) {
        $this->context->buildViolation(
            $constraint->inexistingLocaleMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode(),
                '%locale%'    => $localeCode
            ]
        )->addViolation();
    }

    /**
     * @param LocalizableValue $constraint
     * @param ValueInterface   $value
     */
    protected function addUnexpectedLocaleViolation(LocalizableValue $constraint, ValueInterface $value)
    {
        $this->context->buildViolation(
            $constraint->unexpectedLocaleMessage,
            [
                '%attribute%' => $value->getAttribute()->getCode()
            ]
        )->addViolation();
    }
}
