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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataMappingsValidator extends ConstraintValidator
{
    private const MAX_DATA_MAPPING_COUNT = 500;
    private const MIN_SOURCES_COUNT = 1;
    private const MAX_SOURCES_COUNT = 4;
    private const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    public function __construct(
        private GetAttributes $getAttributes,
    ) {
    }

    public function validate($dataMappings, Constraint $constraint): void
    {
        if (empty($dataMappings)) {
            return;
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($dataMappings, [
            new Type('array'),
            new Count([
                'max' => self::MAX_DATA_MAPPING_COUNT,
                'maxMessage' => DataMappings::MAX_COUNT_REACHED,
            ]),
        ]);
        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        foreach ($dataMappings as $dataMapping) {
            $this->validateDataMapping($validator, $dataMapping);
        }

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateDataMappingUuidsAreUnique($dataMappings);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

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
        $targetCodes = array_reduce($dataMappings, function ($targetCodes, $dataMapping) {
            $targetCodes[] = $dataMapping['target']['code'];
            return $targetCodes;
        }, []);

        // TODO: use a dedicated query to get the identifier attribute code ?
        // It could be nice to dispose of a public api to get identifier attribute code and check the count of this code in our $targetCodes array
        // Without that, we need to fetch attribute one by one (if we fetch all, it merges duplicate codes, so we cannot validate the count)

        $targetedAttributes = [];
        foreach ($targetCodes as $targetCode) {
            $targetedAttribute = $this->getAttributes->forCode($targetCode);
            if (null !== $targetedAttribute) {
                $targetedAttributes[] = $targetedAttribute;
            }
        }

        $targetedIdentifierAttributes = array_filter($targetedAttributes, function (Attribute $attribute) {
            return self::IDENTIFIER_ATTRIBUTE_TYPE === $attribute->type();
        });

        $dataMappingTargetingAnIdentifierCount = count($targetedIdentifierAttributes);

        switch ($dataMappingTargetingAnIdentifierCount) {
            case 0:
                $this->context->buildViolation(DataMappings::NO_IDENTIFIER_TARGET_FOUND)
                    ->addViolation();
                break;
            case 1:
                break;
            default:
                $this->context->buildViolation(DataMappings::TOO_MANY_IDENTIFIER_TARGET_FOUND)
                    ->addViolation();
                break;
        }
    }

    private function validateDataMapping(ValidatorInterface $validator, array $dataMapping): void
    {
        $violations = $validator->validate($dataMapping, new Collection([
            'fields' => [
                'uuid' => [
                    new Uuid(),
                    new NotBlank(),
                ],
                'target' => [
                    new Target(),
                ],
                'sources' => [
                    new Type('array'),
                    new Count([
                        'min' => self::MIN_SOURCES_COUNT,
                        'minMessage' => DataMappings::MIN_SOURCES_COUNT_REACHED,
                        'max' => self::MAX_SOURCES_COUNT,
                        'maxMessage' => DataMappings::MAX_SOURCES_COUNT_REACHED,
                    ]),
                    new Unique([
                        'message' => DataMappings::SOURCES_SHOULD_BE_UNIQUE,
                    ]),
                ],
                'operations' => [
                    new Type('array'),
                ],
                'sampleData' => [
                    new Type('array'),
                ],
            ],
        ]));

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

        // TODO RAB-547: validate that sources are coherent with columns entries
    }
}
