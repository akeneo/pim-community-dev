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

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given asset already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCodeShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $insertedCodes = [];

    public function __construct(
        private AssetExistsInterface $assetExists,
        private int $batchSize=100
    ) {
    }

    public function validate($command, Constraint $constraint)
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
        if (!$command instanceof CreateAssetCommand) {
            throw new \InvalidArgumentException(sprintf(
                'Expected argument to be of class "%s", "%s" given',
                CreateAssetCommand::class,
                $command::class
            ));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AssetCodeShouldBeUnique) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateAssetCommand $command): void
    {
        $code = AssetCode::fromString($command->code);
        $alreadyExists = $this->assetExists->withCode($code);

        if ($alreadyExists
            || in_array(strtolower($command->code), $this->insertedCodes)) {
            $this->context->buildViolation(AssetCodeShouldBeUnique::ERROR_MESSAGE)
                ->setParameter('%code%', (string) $code)
                ->atPath('code')
                ->addViolation();
            return;
        }
        $this->insertedCodes[] = strtolower($command->code);
        if (count($this->insertedCodes) > $this->batchSize) {
            array_shift($this->insertedCodes);
        }
    }
}
