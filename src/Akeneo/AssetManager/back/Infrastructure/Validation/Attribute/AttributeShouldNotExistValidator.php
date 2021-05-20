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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the attribute code given does not already exists
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeShouldNotExistValidator extends ConstraintValidator
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

        $assetFamilyIdentifier = $command->assetFamilyIdentifier;
        $code = $command->code;
        $alreadyExists = $this->attributeExists->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code)
        );

        if ($alreadyExists) {
            $this->context->buildViolation(AttributeShouldNotExist::ERROR_MESSAGE)
                ->setParameter('asset_family_identifier', $assetFamilyIdentifier)
                ->setParameter('code', $code)
                ->atPath('code')
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractCreateAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                AbstractCreateAttributeCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeShouldNotExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
