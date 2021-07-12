<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NullableNamingConventionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        if (null === $command->namingConvention || [] === $command->namingConvention) {
            return;
        }

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->identifier);
        $commandAttributeAsMainMedia = null;
        if ($command instanceof EditAssetFamilyCommand && isset($command->attributeAsMainMedia)) {
            try {
                $commandAttributeAsMainMedia = AttributeCode::fromString($command->attributeAsMainMedia);
            } catch (\InvalidArgumentException $e) {
                // do nothing, this error is handled elsewhere.
            }
        }
        $nestedConstraints = [
            new Assert\Type('array'),
            new NamingConvention($assetFamilyIdentifier, $commandAttributeAsMainMedia),
        ];

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator
            ->atPath('naming_convention')
            ->validate($command->namingConvention, $nestedConstraints, Constraint::DEFAULT_GROUP);
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof NullableNamingConvention) {
            throw new UnexpectedTypeException($constraint, NullableNamingConvention::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof CreateAssetFamilyCommand && !$command instanceof EditAssetFamilyCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s" or "%s", "%s" given',
                    CreateAssetFamilyCommand::class,
                    EditAssetFamilyCommand::class,
                    get_class($command)
                )
            );
        }
    }
}
