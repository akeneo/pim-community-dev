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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Query\IsMultiSourceTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\Operation\CleanHTMLTagsOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DataMappingsValidator extends ConstraintValidator
{
    private const MAX_DATA_MAPPING_COUNT = 500;
    private const MIN_SOURCES_COUNT = 1;
    private const MAX_SOURCES_COUNT = 4;
    private const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    public function __construct(
        private GetAttributes $getAttributes,
        private IsMultiSourceTarget $isMultiSourceTarget,
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

        if ($this->isThereViolations() || empty($dataMappings)) {
            return;
        }

        $columns = $dataMappingsConstraint->getColumns();
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
                } else {
                    $dataMappingUuids[] = $dataMapping['uuid'];
                }
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
        $validator = $this->context->getValidator();
        $violations = $validator->validate($dataMapping, new Collection([
            'fields' => [
                'uuid' => [
                    new Uuid(),
                    new NotBlank(),
                ],
                'target' => new Target(),
                'sources' => [
                    new Type('array'),
                    new Unique([
                        'message' => DataMappings::SOURCES_SHOULD_BE_UNIQUE,
                    ]),
                ],
                //TODO we need to validate by target type as we did in Tailored Export
                'operations' => new Collection(['fields' => [
                    CleanHTMLTagsOperation::TYPE => new Optional(new CleanHTMLTagsOperationConstraint()),
                ]]),
                'sample_data' => [
                    new Type('array'),
                ],
            ],
        ]));

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $builder = $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath(sprintf('[%s]%s', $dataMapping['uuid'] ?? 'null', $violation->getPropertyPath()))
                    ->setInvalidValue($violation->getInvalidValue());
                if ($violation->getPlural()) {
                    $builder->setPlural((int)$violation->getPlural());
                }
                $builder->addViolation();
            }

            return;
        }

        $this->validateSourcesCount($dataMapping);
        $this->validateSourcesExist($dataMapping, $columns);
    }

    private function validateSourcesCount(array $dataMapping): void
    {
        $targetType = $dataMapping['target']['type'];
        $targetCode = $dataMapping['target']['code'];

        $isMultiSourceTarget = AttributeTarget::TYPE === $targetType ?
            $this->isMultiSourceTarget->isAttributeMultiSourceTarget($targetCode) :
            $this->isMultiSourceTarget->isSystemPropertyMultiSourceTarget($targetCode);

        $maxSourcesCount = $isMultiSourceTarget ? self::MAX_SOURCES_COUNT : self::MIN_SOURCES_COUNT;

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath(sprintf('[%s][sources]', $dataMapping['uuid'] ?? 'null'))
            ->validate($dataMapping['sources'], new Count([
                'min' => self::MIN_SOURCES_COUNT,
                'minMessage' => DataMappings::MIN_SOURCES_COUNT_REACHED,
                'max' => $maxSourcesCount,
                'maxMessage' => DataMappings::MAX_SOURCES_COUNT_REACHED,
                'exactMessage' => DataMappings::SOURCES_COUNT_MISMATCHED,
            ]));
    }

    private function validateSourcesExist(array $dataMapping, array $columns): void
    {
        $columnsUuid = array_map(static fn (array $column) => $column['uuid'], $columns);

        foreach ($dataMapping['sources'] as $source) {
            if (!in_array($source, $columnsUuid)) {
                $this->context->buildViolation(
                    DataMappings::SOURCES_SHOULD_EXIST,
                )
                    ->atPath(sprintf('[%s][sources]', $dataMapping['uuid']))
                    ->addViolation();

                return;
            }
        }
    }

    private function isThereViolations(): bool
    {
        return 0 < $this->context->getViolations()->count();
    }
}
