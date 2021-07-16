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

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention\Pattern;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention\RawSource;
use Akeneo\AssetManager\Infrastructure\Validation\Channel\RawChannelShouldExist;
use Akeneo\AssetManager\Infrastructure\Validation\Locale\RawLocaleShouldBeActivated;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NamingConventionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($normalizedNamingConvention, Constraint $constraint): void
    {
        if (!$constraint instanceof NamingConvention) {
            throw new UnexpectedTypeException($constraint, NamingConvention::class);
        }

        $assetFamilyIdentifier = $constraint->getAssetFamilyIdentifier();

        $constraint = new Assert\Collection([
            'source' => [
                new Assert\Type('array'),
                new Assert\Collection([
                    'property' => [
                        new Assert\Type('string'),
                        new Assert\NotNull(),
                    ],
                    'locale' => new RawLocaleShouldBeActivated(),
                    'channel' => new RawChannelShouldExist(),
                ]),
                new RawSource($assetFamilyIdentifier, $constraint->getAttributeAsMainMedia()),
            ],
            'pattern' => new Pattern(),
            'abort_asset_creation_on_error' => new Assert\Type('bool'),
        ]);

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator->validate($normalizedNamingConvention, $constraint, Constraint::DEFAULT_GROUP);
    }
}
