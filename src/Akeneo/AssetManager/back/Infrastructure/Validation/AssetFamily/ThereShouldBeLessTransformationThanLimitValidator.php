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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

class ThereShouldBeLessTransformationThanLimitValidator extends ConstraintValidator
{
    private int $maxTransformationByAssetFamilyLimit;

    public function __construct(int $maxTransformationByAssetFamilyLimit)
    {
        $this->maxTransformationByAssetFamilyLimit = $maxTransformationByAssetFamilyLimit;
    }

    public function validate($command, Constraint $constraint): void
    {
        $this->checkCommandType($command);
        if (!$constraint instanceof ThereShouldBeLessTransformationThanLimit) {
            throw new UnexpectedTypeException($constraint, ThereShouldBeLessTransformationThanLimit::class);
        }

        if (null === $command->transformations) {
            return;
        }

        $total = count($command->transformations);

        if ($total > $this->maxTransformationByAssetFamilyLimit) {
            $this->context->buildViolation(ThereShouldBeLessTransformationThanLimit::ERROR_MESSAGE)
                ->setParameter('%asset_family_identifier%', $command->identifier)
                ->setParameter('%limit%', $this->maxTransformationByAssetFamilyLimit)
                ->atPath('transformations')
                ->addViolation();
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
