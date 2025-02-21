<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Export;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2025 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductFiltersValidator extends ConstraintValidator
{
    public function __construct(
        private GetAttributes $getAttributes,
        private array $attributeFilterConstraintsByType,
        private array $propertyFilters,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ProductFilters::class);
        if (!array($value)) {
            return;
        }

        foreach ($value as $i => $filter) {
            if (!isset($filter['field']) || in_array($filter['field'], $this->propertyFilters)) {
                continue;
            }

            $this->validateAttributeFilter($filter, sprintf('[%d]', $i));
        }
    }

    private function validateAttributeFilter(array $filter, string $path): void
    {
        $attributeCode = FieldFilterHelper::getCode($filter['field']);
        $attribute = $this->getAttributes->forCode($attributeCode);

        if (!$attribute instanceof Attribute) {
            return;
        }

        $constraint = $this->attributeFilterConstraintsByType[$attribute->type()] ?? null;
        if (!$constraint instanceof Constraint) {
            return;
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->atPath($path)
            ->validate($filter, $constraint);
    }
}
