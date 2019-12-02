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
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\OperationShouldBeInstantiable;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\RawSourceExist;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\RawTargetExist;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\TransformationCanNotHaveSameOperationTwice;
use Akeneo\AssetManager\Infrastructure\Validation\Channel\RawChannelShouldExist;
use Akeneo\AssetManager\Infrastructure\Validation\Locale\RawLocaleShouldBeActivated;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransformationCollectionValidator extends ConstraintValidator
{
    private const FILENAME_REGEX = '/^[\w-\. ]*$/';

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->identifier);

        $constraint = [
            new Assert\Type('array'),
            new Assert\All([
                new Assert\Collection([
                    'source' => [
                        new Assert\Collection([
                            'attribute' => new Assert\NotNull(),
                            'locale' => new RawLocaleShouldBeActivated(),
                            'channel' => new RawChannelShouldExist(),
                        ]),
                        new RawSourceExist($assetFamilyIdentifier),
                    ],
                    'target' => [
                        new Assert\Collection([
                            'attribute' => new Assert\NotNull(),
                            'locale' => new RawLocaleShouldBeActivated(),
                            'channel' => new RawChannelShouldExist(),
                        ]),
                        new RawTargetExist($assetFamilyIdentifier),
                    ],
                    'operations' => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Collection([
                                'type' => new Assert\NotNull(),
                                'parameters' => new Assert\Type('array'),
                            ]),
                            new OperationShouldBeInstantiable(),
                        ]),
                        new TransformationCanNotHaveSameOperationTwice($assetFamilyIdentifier),
                    ],
                    'filename_prefix' => [
                        new Assert\Type('string'),
                        new Assert\Regex([
                            'pattern' => self::FILENAME_REGEX,
                            'message' => "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space.",
                        ]),
                    ],
                    'filename_suffix' => [
                        new Assert\Type('string'),
                        new Assert\Regex([
                            'pattern' => self::FILENAME_REGEX,
                            'message' => "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space.",
                        ]),
                    ],
                ]),
            ]),
        ];

        $violations = $this->validator->validate($command->transformations, $constraint);
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof TransformationCollection) {
            throw new UnexpectedTypeException($constraint, TransformationCollection::class);
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
