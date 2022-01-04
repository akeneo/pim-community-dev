<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Akeneo\AssetManager\Application\Attribute\AppendAttributeOption\AppendAttributeOptionCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AttributeOptionShouldExistValidator extends ConstraintValidator
{
    public function __construct(private GetAttributeIdentifierInterface $getAttributeIdentifier, private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof EditAttributeOptionCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditAttributeOptionCommand::class,
                    $command::class
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeOptionShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(EditAttributeOptionCommand $command): void
    {
        $identifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->attributeCode)
        );
        $attribute = $this->attributeRepository->getByIdentifier($identifier);

        $optionCode = OptionCode::fromString($command->optionCode);
        if (!$attribute->hasAttributeOption($optionCode)) {
            $this->context->buildViolation(AttributeOptionShouldExist::ERROR_MESSAGE)
                ->atPath('code')
                ->addViolation();
        }
    }
}
