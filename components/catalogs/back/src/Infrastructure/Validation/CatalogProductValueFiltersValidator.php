<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsActivatedCurrency;
use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsActivatedLocale;
use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsValidChannel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CatalogProductValueFiltersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogProductValueFilters) {
            throw new UnexpectedTypeException($constraint, CatalogProductValueFilters::class);
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints());
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getConstraints(): array
    {
        return [
            new Assert\Collection([
                'channels' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        'constraints' => [
                            new Assert\Type('string'),
                            new FilterContainsValidChannel(),
                        ],
                    ]),
                ]),
                'locales' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        'constraints' => [
                            new Assert\Type('string'),
                            new FilterContainsActivatedLocale(),
                        ],
                    ]),
                ]),
                'currencies' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        'constraints' => [
                            new Assert\Type('string'),
                            new FilterContainsActivatedCurrency(),
                        ],
                    ]),
                ]),
            ]),
        ];
    }
}
