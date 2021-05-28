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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessAssetFamilyThanLimitValidator extends ConstraintValidator
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private int $assetFamilyLimit;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        int $assetFamilyLimit
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetFamilyLimit = $assetFamilyLimit;
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
        if (!$command instanceof CreateAssetFamilyCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    CreateAssetFamilyCommand::class,
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
        if (!$constraint instanceof ThereShouldBeLessAssetFamilyThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateAssetFamilyCommand $command): void
    {
        $total = $this->assetFamilyRepository->count();

        if ($total >= $this->assetFamilyLimit) {
            $this->context->buildViolation(ThereShouldBeLessAssetFamilyThanLimit::ERROR_MESSAGE)
                ->setParameter('%asset_family_identifier%', $command->identifier)
                ->setParameter('%limit%', $this->assetFamilyLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}
