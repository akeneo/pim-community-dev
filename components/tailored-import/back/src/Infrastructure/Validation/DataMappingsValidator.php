<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\TargetConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

class DataMappingsValidator extends ConstraintValidator
{
    private const MAX_DATA_MAPPING_COUNT = 500;
    private const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    public function __construct(
        private GetAttributes $getAttributes,
        private array $attributeConstraints,
        private array $propertyConstraints,
    ) {
    }

    public function validate($dataMappings, Constraint $dataMappingsConstraint): void
    {
        if (!$dataMappingsConstraint instanceof DataMappings) {
            throw new UnexpectedTypeException($dataMappingsConstraint, DataMappings::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($dataMappings, [
            new Type('array'),
            new Count([
                'max' => self::MAX_DATA_MAPPING_COUNT,
                'maxMessage' => DataMappings::MAX_COUNT_REACHED,
            ]),
        ]);

        $columns = $dataMappingsConstraint->getColumnUuids();
        foreach ($dataMappings as $dataMapping) {
            $this->validateDataMapping($dataMapping, $columns);
        }

        if ($this->isThereViolations()) {
            return;
        }

        $this->validateDataMappingUuidsAreUnique($dataMappings);
        $this->validateThereIsOneDataMappingTargetingAnIdentifier($dataMappings);
    }

    private function validateDataMappingUuidsAreUnique(array $dataMappings): void
    {
        $dataMappingUuids = [];
        foreach ($dataMappings as $dataMapping) {
            if (isset($dataMapping['uuid'])) {
                if (in_array($dataMapping['uuid'], $dataMappingUuids)) {
                    $this->context->buildViolation(DataMappings::UUID_SHOULD_BE_UNIQUE)
                        ->atPath(sprintf('[%s][uuid]', $dataMapping['uuid']))
                        ->setInvalidValue($dataMapping['uuid'])
                        ->addViolation();
                }

                $dataMappingUuids[] = $dataMapping['uuid'];
            }
        }
    }

    private function validateThereIsOneDataMappingTargetingAnIdentifier(array $dataMappings): void
    {
        $attributeTargetCodes = array_reduce($dataMappings, function ($targetCodes, $dataMapping) {
            if ('attribute' === $dataMapping['target']['type']) {
                $targetCodes[] = $dataMapping['target']['code'];
            }

            return $targetCodes;
        }, []);

        $countByTargetCode = array_count_values($attributeTargetCodes);

        $targetedAttributes = $this->getAttributes->forCodes($attributeTargetCodes);
        $targetedIdentifierAttribute = current(array_filter($targetedAttributes, function (?Attribute $attribute) {
            return null !== $attribute && self::IDENTIFIER_ATTRIBUTE_TYPE === $attribute->type();
        }));

        $dataMappingTargetingAnIdentifierCount = $targetedIdentifierAttribute ? $countByTargetCode[$targetedIdentifierAttribute->code()] : 0;

        switch (true) {
            case 0 === $dataMappingTargetingAnIdentifierCount:
                $this->context->buildViolation(DataMappings::NO_IDENTIFIER_TARGET_FOUND)
                    ->addViolation();
                break;
            case 1 < $dataMappingTargetingAnIdentifierCount:
                $this->context->buildViolation(DataMappings::TOO_MANY_IDENTIFIER_TARGET_FOUND)
                    ->addViolation();
                break;
        }
    }

    private function validateDataMapping(array $dataMapping, array $columns): void
    {
        $targetType = $dataMapping['target']['type'] ?? null;
        match ($targetType) {
            AttributeTarget::TYPE => $this->validateAttributeDataMapping($dataMapping, $columns),
            PropertyTarget::TYPE => $this->validatePropertyDataMapping($dataMapping, $columns),
            default => throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $targetType)),
        };
    }

    private function validateAttributeDataMapping(array $dataMapping, array $columns): void
    {
        $dataMappingUuid = $dataMapping['uuid'] ?? null;
        $attributeCode = $dataMapping['target']['code'] ?? null;

        Assert::notNull($attributeCode);
        Assert::notNull($dataMappingUuid);

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (!$attribute instanceof Attribute) {
            $this->context->buildViolation(
                DataMappings::ATTRIBUTE_SHOULD_EXIST,
                [
                    '{{ attribute_code }}' => $attributeCode,
                ],
            )
                ->atPath(sprintf('[%s][target][code]', $dataMappingUuid))
                ->addViolation();

            return;
        }

        $constraintClass = $this->attributeConstraints[$attribute->type()] ?? null;
        if (!$this->isTargetConstraint($constraintClass)) {
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath(sprintf('[%s]', $dataMappingUuid))
            ->validate($dataMapping, new $constraintClass($columns, $attribute));
    }

    private function validatePropertyDataMapping(array $dataMapping, array $columns): void
    {
        $propertyCode = $dataMapping['target']['code'];
        $constraintClass = $this->propertyConstraints[$propertyCode] ?? null;
        if (!$this->isTargetConstraint($constraintClass)) {
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath(sprintf('[%s]', $dataMapping['uuid']))
            ->validate($dataMapping, new $constraintClass($columns));
    }

    private function isTargetConstraint(?string $constraintClass): bool
    {
        return is_subclass_of($constraintClass, TargetConstraint::class, true);
    }

    private function isThereViolations(): bool
    {
        return 0 < $this->context->getViolations()->count();
    }
}
