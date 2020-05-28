<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValueInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RecordsShouldExistValidator extends ConstraintValidator
{
    /** @var FindExistingRecordCodesInterface */
    private $findExistingRecordCodes;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var string[] */
    private $referenceEntityByAttributeCode = [];

    public function __construct(
        FindExistingRecordCodesInterface $findExistingRecordCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->findExistingRecordCodes = $findExistingRecordCodes;
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof RecordsShouldExist) {
            throw new UnexpectedTypeException($constraint, RecordsShouldExist::class);
        }

        if (!$values instanceof WriteValueCollection) {
            return;
        }

        $refEntityValues = $values->filter(
            function (ValueInterface $value): bool {
                return ($value instanceof ReferenceEntityValueInterface ||
                    $value instanceof ReferenceEntityCollectionValueInterface)
                    && null !== $value->getData()
                    && [] !== $value->getData();
            }
        );

        if ($refEntityValues->isEmpty()) {
            return;
        }
        $existingRecordCodes = $this->getExistingRecordCodesIndexedByReferenceEntity($refEntityValues);

        foreach ($refEntityValues as $key => $value) {
            $attributeCode = $value->getAttributeCode();
            $refEntity = $this->getReferenceEntityName($attributeCode);

            if ($value instanceof ReferenceEntityValueInterface) {
                if (!in_array($value->getData()->__toString(), $existingRecordCodes[$refEntity])) {
                    $this->context->buildViolation(
                        $constraint->message,
                        [
                            '%attribute_code%' => $attributeCode,
                            '%invalid_record%' => $value->getData()->__toString(),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            } elseif ($value instanceof ReferenceEntityCollectionValueInterface) {
                $notExistingRecordCodes = array_diff(
                    array_map(
                        function (RecordCode $recordcode): string {
                            return $recordcode->__toString();
                        },
                        $value->getData()
                    ),
                    $existingRecordCodes[$refEntity]
                );
                if (!empty($notExistingRecordCodes)) {
                    $this->context->buildViolation(
                        $constraint->messagePlural,
                        [
                            '%attribute_code%' => $attributeCode,
                            '%invalid_records%' => implode(', ', $notExistingRecordCodes),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            }
        }
    }

    private function getExistingRecordCodesIndexedByReferenceEntity(WriteValueCollection $values): array
    {
        $recordCodesIndexedByReferenceEntity = [];
        foreach ($values as $value) {
            $referenceEntity = $this->getReferenceEntityName($value->getAttributeCode());
            if (!isset($recordCodesIndexedByReferenceEntity[$referenceEntity])) {
                $recordCodesIndexedByReferenceEntity[$referenceEntity] = [];
            }

            if ($value instanceof ReferenceEntityValueInterface) {
                $recordCodesIndexedByReferenceEntity[$referenceEntity][] = $value->getData()->__toString();
            } elseif ($value instanceof ReferenceEntityCollectionValueInterface) {
                $recordCodesIndexedByReferenceEntity[$referenceEntity] = array_merge(
                    $recordCodesIndexedByReferenceEntity[$referenceEntity],
                    array_map(
                        function (RecordCode $recordCode): string {
                            return $recordCode->__toString();
                        },
                        $value->getData()
                    )
                );
            }
        }

        $existingCodesIndexedByRefEntity = [];
        foreach ($recordCodesIndexedByReferenceEntity as $refEntity => $requestedRecordCodes) {
            $existingCodesIndexedByRefEntity[$refEntity] = $this->findExistingRecordCodes->find(
                ReferenceEntityIdentifier::fromString($refEntity),
                array_values(array_unique($requestedRecordCodes))
            );
        }

        return $existingCodesIndexedByRefEntity;
    }

    /**
     * Given an attribute code, returns the associated reference entity
     */
    private function getReferenceEntityName(string $attributeCode): string
    {
        if (!isset($this->referenceEntityByAttributeCode[$attributeCode])) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $this->referenceEntityByAttributeCode[$attributeCode] =  $attribute->getReferenceDataName();
        }

        return $this->referenceEntityByAttributeCode[$attributeCode];
    }
}
