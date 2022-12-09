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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityAttributeSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection\CodeLabelSelectionConstraint;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\AttributeDetails;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindReferenceEntityAttributesInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class ReferenceEntitySelectionValidator extends ConstraintValidator
{
    public function __construct(
        private FindReferenceEntityAttributesInterface $findReferenceEntityAttributes,
        private array $supportedAttributeTypes,
        private array $availableDecimalSeparators,
    ) {
    }

    public function validate($selection, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($selection, new Collection(
            [
                'fields' => [
                    'type' => new Choice([
                        ReferenceEntityCodeSelection::TYPE,
                        ReferenceEntityLabelSelection::TYPE,
                        ReferenceEntityAttributeSelectionInterface::TYPE,
                    ]),
                    'channel' => new Optional(new Type('string')),
                    'locale' => new Optional(new Type('string')),
                    'attribute_identifier' => new Optional(new Type('string')),
                    'attribute_type' => new Optional(new Choice($this->supportedAttributeTypes)),
                    'reference_entity_code' => new Optional(new Type('string')),
                    'decimal_separator' => new Optional(new Choice($this->availableDecimalSeparators)),
                    'option_selection' => new Optional(new CodeLabelSelectionConstraint()),
                ],
            ],
        ));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if (ReferenceEntityAttributeSelectionInterface::TYPE === $selection['type']) {
            $this->validateAttributeExists($selection);
        }
    }

    private function validateAttributeExists(array $selection): void
    {
        $existingAttributes = $this->findReferenceEntityAttributes->findByCode($selection['reference_entity_code']);
        $selectionAttribute = current(array_filter(
            $existingAttributes,
            static fn (AttributeDetails $attribute) => $attribute->identifier === $selection['attribute_identifier'],
        ));

        if (false === $selectionAttribute) {
            $this->context->buildViolation(ReferenceEntitySelectionConstraint::ATTRIBUTE_NOT_FOUND)
                ->atPath('[type]')
                ->addViolation();
        }
    }
}
