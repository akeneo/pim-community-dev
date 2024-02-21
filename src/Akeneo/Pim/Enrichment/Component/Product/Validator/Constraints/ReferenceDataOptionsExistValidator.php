<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataOptionsExistValidator extends ConstraintValidator
{
    /** @var GetExistingReferenceDataCodes */
    private $getExistingReferenceDataCodes;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->getExistingReferenceDataCodes = $getExistingReferenceDataCodes;
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof ReferenceDataOptionsExist) {
            throw new UnexpectedTypeException($constraint, ReferenceDataOptionsExist::class);
        }

        if (!($values instanceof WriteValueCollection)) {
            return;
        }

        $refDataValues = $values->filter(
            function (ValueInterface $value): bool {
                return $value instanceof ReferenceDataCollectionValueInterface || $value instanceof ReferenceDataValueInterface;
            }
        );

        if ($refDataValues->isEmpty()) {
            return;
        }

        $referenceDataNames = $this->getReferenceDataNamesIndexedByAttributeCode($refDataValues->getAttributeCodes());

        $existingRefDataCodes = $this->getExistingReferenceDadaCodesIndexedByAttributeCodes($refDataValues, $referenceDataNames);
        array_walk_recursive($existingRefDataCodes, function (string &$value) {
            $value = strtolower($value);
        });

        foreach ($refDataValues as $key => $value) {
            if ($value instanceof ReferenceDataValueInterface) {
                if (!in_array(strtolower($value->getData()), ($existingRefDataCodes[$value->getAttributeCode()] ?? []))) {
                    $this->context->buildViolation(
                        $constraint->message,
                        [
                            '%attribute_code%' => $value->getAttributeCode(),
                            '%reference_data_name%' => $referenceDataNames[$value->getAttributeCode()],
                            '%invalid_code%' => $value->getData(),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            } elseif ($value instanceof ReferenceDataCollectionValueInterface) {
                $notExistingRefDataCodes = array_diff(
                    array_map('strtolower', $value->getData()),
                    ($existingRefDataCodes[$value->getAttributeCode()] ?? [])
                );
                if (!empty($notExistingRefDataCodes)) {
                    $this->context->buildViolation(
                        $constraint->messagePlural,
                        [
                            '%attribute_code%' => $value->getAttributeCode(),
                            '%reference_data_name%' => $referenceDataNames[$value->getAttributeCode()],
                            '%invalid_codes%' => implode(', ', $notExistingRefDataCodes),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            }
        }
    }

    private function getExistingReferenceDadaCodesIndexedByAttributeCodes(WriteValueCollection $values, array $referenceDataNames): array
    {
        $optionCodesIndexedByReferenceDataName = [];
        foreach ($values as $value) {
            $referenceDataName = $referenceDataNames[$value->getAttributeCode()];
            $optionCodesIndexedByReferenceDataName[$referenceDataName][] =
                $value instanceof ReferenceDataValueInterface ? [$value->getData()] : $value->getData();
        }

        $existingReferenceDataCodes = [];
        foreach ($optionCodesIndexedByReferenceDataName as $refDataName => $refDataCodes) {
            $existingReferenceDataCodes[$refDataName] = $this->getExistingReferenceDataCodes
                ->fromReferenceDataNameAndCodes($refDataName, array_values(array_unique(array_merge_recursive(...$refDataCodes))));
        }

        return array_map(function (string $referenceDataName) use ($existingReferenceDataCodes): array {
            return $existingReferenceDataCodes[$referenceDataName];
        }, $referenceDataNames);
    }

    private function getReferenceDataNamesIndexedByAttributeCode(array $attributeCodes): array
    {
        $referenceDataNames = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $referenceDataNames[$attributeCode] = $attribute->getReferenceDataName();
        }

        return $referenceDataNames;
    }
}
