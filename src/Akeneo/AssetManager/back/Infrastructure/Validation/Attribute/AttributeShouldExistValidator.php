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

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the attribute identifier given does not already exists
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeShouldExistValidator extends ConstraintValidator
{
    private AttributeExistsInterface $attributeExists;

    public function __construct(AttributeExistsInterface $attributeExists)
    {
        $this->attributeExists = $attributeExists;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $attributeExists = $this->attributeExists->withIdentifier(
            AttributeIdentifier::fromString($command->identifier)
        );

        if (!$attributeExists) {
            $this->context->buildViolation(AttributeShouldExist::ERROR_MESSAGE)
                ->atPath('code')
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractEditAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                AbstractCreateAttributeCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
