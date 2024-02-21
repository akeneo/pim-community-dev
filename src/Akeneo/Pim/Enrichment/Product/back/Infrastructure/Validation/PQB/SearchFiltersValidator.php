<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCategories;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCriterion;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedPropertiesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateProperties;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SearchFiltersValidator extends ConstraintValidator
{
    public function __construct(
        private ValidateCriterion $validateCriterion,
        private ValidateCategories $validateCategories,
        private ValidateProperties $validateProperties,
        private ValidateGrantedCategoriesInterface $validateGrantedCategories,
        private ValidateGrantedPropertiesInterface $validateGrantedProperties
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($searchFilters, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, SearchFilters::class);
        if (!\is_array($searchFilters)) {
            return;
        }

        try {
            $this->validateCriterion->validate($searchFilters);
            $this->validateCategories->validate($searchFilters);
            $this->validateGrantedCategories->validate($searchFilters);
            $this->validateProperties->validate($searchFilters);
            $this->validateGrantedProperties->validate($searchFilters);
        } catch (InvalidQueryException $e) {
            $this->context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
