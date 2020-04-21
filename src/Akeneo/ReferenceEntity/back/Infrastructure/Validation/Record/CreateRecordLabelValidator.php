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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validates the label values during the creation of a record.
 * It acts like a proxy: it creates some EditTextValueCommand, each one by label values, and validates them.
 * Ideally the commands are created in the CreateRecordCommand but this is not possible without BC break.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class CreateRecordLabelValidator extends ConstraintValidator
{
    /** @var FindReferenceEntityAttributeAsLabelInterface */
    private $findAttributeAsLabel;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $sqlFindAttributesIndexedByIdentifier;

    /** @var EditValueCommandFactoryRegistryInterface */
    private $editValueCommandFactoryRegistry;

    public function __construct(
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry
    ) {
        $this->findAttributeAsLabel = $findAttributeAsLabel;
        $this->sqlFindAttributesIndexedByIdentifier = $sqlFindAttributesIndexedByIdentifier;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
    }

    public function validate($command, Constraint $constraint)
    {
        Assert::isInstanceOf($command, CreateRecordCommand::class);
        Assert::isInstanceOf($constraint, CreateRecordLabel::class);

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $editValueCommands = $this->getEditLabelCommands($command);
        foreach ($editValueCommands as $editValueCommand) {
            $validator->validate(
                $editValueCommand,
                new EditTextValueCommand(['groups' => $constraint->groups])
            );
        }
    }

    /**
     * @param CreateRecordCommand $command
     * @return AbstractEditValueCommand[]
     */
    private function getEditLabelCommands(CreateRecordCommand $command): array
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);
        /** @var AttributeAsLabelReference $attributeAsLabelReference */
        $attributeAsLabelReference = $this->findAttributeAsLabel->find($referenceEntityIdentifier);
        if ($attributeAsLabelReference->isEmpty()) {
            return [];
        }

        $labelAttributeIdentifier = $attributeAsLabelReference->getIdentifier();
        $attributesIndexedByIdentifier = $this->sqlFindAttributesIndexedByIdentifier->find($referenceEntityIdentifier);

        $editValueCommands = [];
        foreach ($command->labels as $locale => $label) {
            if (null === $label) {
                continue;
            }

            $attribute = $attributesIndexedByIdentifier[$labelAttributeIdentifier->normalize()];
            $normalizedValue = [
                'attribute' => $labelAttributeIdentifier->normalize(),
                'channel' => null,
                'locale' => $locale,
                'data' => $label,
            ];
            $editValueCommands[] = $this->editValueCommandFactoryRegistry
                ->getFactory($attribute, $normalizedValue)
                ->create($attribute, $normalizedValue);
        }

        return $editValueCommands;
    }
}
