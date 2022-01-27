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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataMappingsValidator extends ConstraintValidator
{
    private const MAX_DATA_MAPPING_COUNT = 500;
    private const DATA_MAPPING_MIN_SOURCES_COUNT = 1;
    private const DATA_MAPPING_MAX_SOURCES_COUNT = 4;

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
                'maxMessage' => DataMappings::MAX_DATA_MAPPING_COUNT_REACHED,
            ]),
        ]);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        foreach ($dataMappings as $dataMapping) {
            $this->validateDataMapping($validator, $dataMapping);
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
                        'min' => self::DATA_MAPPING_MIN_SOURCES_COUNT,
                        'minMessage' => DataMappings::DATA_MAPPING_MIN_SOURCES_COUNT_REACHED,
                        'max' => self::DATA_MAPPING_MAX_SOURCES_COUNT,
                        'maxMessage' => DataMappings::DATA_MAPPING_MAX_SOURCES_COUNT_REACHED,
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

        $this->buildDataMappingViolations($violations, $dataMapping['uuid']);

        //TODO RAB-547 validate that sources are coherent with columns entries
    }

    private function buildDataMappingViolations(ConstraintViolationListInterface $violations, string $dataMappingUuid): void
    {
        foreach ($violations as $violation) {
            $builder = $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath(sprintf('[%s]%s', $dataMappingUuid, $violation->getPropertyPath()))
                ->setInvalidValue($violation->getInvalidValue());

            if ($violation->getPlural()) {
                $builder->setPlural((int)$violation->getPlural());
            }

            $builder->addViolation();
        }
    }
}
