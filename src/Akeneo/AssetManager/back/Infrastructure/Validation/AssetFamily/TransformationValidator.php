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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
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

/**
 * Validate a transformation coming from storage.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationValidator extends ConstraintValidator
{
    public const FILENAME_REGEX = '/^[\w\-\. ]*$/';

    /**
     * {@inheritdoc}
     */
    public function validate($normalizedTransformation, Constraint $constraint): void
    {
        if (!$constraint instanceof Transformation) {
            throw new UnexpectedTypeException($constraint, Transformation::class);
        }

        $assetFamilyIdentifier = $constraint->getAssetFamilyIdentifier();

        $constraint = new Assert\Collection([
            'label' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
            ],
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
                        'parameters' => new Assert\Optional(new Assert\Type('array')),
                    ]),
                    new OperationShouldBeInstantiable(),
                ]),
                new TransformationCanNotHaveSameOperationTwice($assetFamilyIdentifier),
            ],
            'filename_prefix' => new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => self::FILENAME_REGEX,
                    'message' => "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space.",
                ]),
            ]),
            'filename_suffix' =>  new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => self::FILENAME_REGEX,
                    'message' => "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space.",
                ]),
            ]),
            'updated_at' => new Assert\Optional(new Assert\Type('string')),
        ]);

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator->validate($normalizedTransformation, $constraint, Constraint::DEFAULT_GROUP);
    }
}
