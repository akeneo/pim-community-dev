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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given asset_family already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyCodeShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var AssetFamilyExistsInterface */
    private $assetFamilyExists;

    public function __construct(AssetFamilyExistsInterface $assetExists)
    {
        $this->assetFamilyExists = $assetExists;
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
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                CreateAssetFamilyCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AssetFamilyCodeShouldBeUnique) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateAssetFamilyCommand $command): void
    {
        $assetFamilyIdentifier = $command->code;
        $alreadyExists = $this->assetFamilyExists->withIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
        );
        if ($alreadyExists) {
            $this->context->buildViolation(AssetFamilyCodeShouldBeUnique::ERROR_MESSAGE)
                ->setParameter('%asset_family_identifier%', $assetFamilyIdentifier)
                ->atPath('code')
                ->addViolation();
        }
    }
}
