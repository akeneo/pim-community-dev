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
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessAttributesThanLimitValidator extends ConstraintValidator
{
    private AttributeRepositoryInterface $attributeRepository;

    private int $attributesLimit;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        int $attributesLimit
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributesLimit = $attributesLimit;
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
        if (!$command instanceof AbstractCreateAttributeCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    AbstractCreateAttributeCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ThereShouldBeLessAttributesThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(AbstractCreateAttributeCommand $command): void
    {
        $total = $this->attributeRepository->countByAssetFamily(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier)
        );

        if ($total >= $this->attributesLimit) {
            $this->context->buildViolation(ThereShouldBeLessAttributesThanLimit::ERROR_MESSAGE)
                ->setParameter('%attribute_label%', current($command->labels))
                ->setParameter('%limit%', $this->attributesLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}
