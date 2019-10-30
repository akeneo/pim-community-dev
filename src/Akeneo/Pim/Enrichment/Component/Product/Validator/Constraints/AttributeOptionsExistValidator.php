<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsExistValidator extends ConstraintValidator
{
    /** @var GetExistingAttributeOptionCodes */
    private $getExistingAttibuteOptionCodes;

    public function __construct(GetExistingAttributeOptionCodes $getExistingAttibuteOptionCodes)
    {
        $this->getExistingAttibuteOptionCodes = $getExistingAttibuteOptionCodes;
    }

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeOptionsExist) {
            throw new UnexpectedTypeException($constraint, AttributeOptionsExist::class);
        }

        if (!($values instanceof WriteValueCollection)) {
            return;
        }

        $optionValues = $values->filter(
            function (ValueInterface $value): bool {
                return $value instanceof OptionValueInterface || $value instanceof OptionsValueInterface;
            }
        );

        if ($optionValues->isEmpty()) {
            return;
        }

        $existingOptionCodes = $this->getExistingOptionCodesIndexedByAttributeCodes($optionValues);
        array_walk_recursive($existingOptionCodes, function (string &$value) {
            $value = strtolower($value);
        });

        foreach ($optionValues as $key => $value) {
            if ($value instanceof OptionValueInterface) {
                if (!in_array(strtolower($value->getData()), ($existingOptionCodes[$value->getAttributeCode()] ?? []))) {
                    $this->context->buildViolation(
                        $constraint->message,
                        [
                            '%attribute_code%' => $value->getAttributeCode(),
                            '%invalid_option%' => $value->getData(),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            } elseif ($value instanceof OptionsValueInterface) {
                $notExistingOptionCodes = array_diff(array_map('strtolower', $value->getData()), ($existingOptionCodes[$value->getAttributeCode()] ?? []));
                if (!empty($notExistingOptionCodes)) {
                    $this->context->buildViolation(
                        $constraint->messagePlural,
                        [
                            '%attribute_code%' => $value->getAttributeCode(),
                            '%invalid_options%' => implode(', ', $notExistingOptionCodes),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            }
        }
    }

    private function getExistingOptionCodesIndexedByAttributeCodes(WriteValueCollection $values): array
    {
        $optionCodesIndexedByAttributeCode = [];
        foreach ($values as $value) {
            $attributeCode = $value->getAttributeCode();

            if (!isset($optionCodesIndexedByAttributeCode[$attributeCode])) {
                $optionCodesIndexedByAttributeCode[$attributeCode] = [];
            }

            if ($value instanceof OptionValueInterface) {
                $optionCodesIndexedByAttributeCode[$attributeCode][] = $value->getData();
            } elseif ($value instanceof OptionsValueInterface) {
                $optionCodesIndexedByAttributeCode[$attributeCode] = array_merge(
                    $optionCodesIndexedByAttributeCode[$attributeCode],
                    $value->getData()
                );
            }
        }

        return $this->getExistingAttibuteOptionCodes->fromOptionCodesByAttributeCode(
            array_filter($optionCodesIndexedByAttributeCode)
        );
    }
}
