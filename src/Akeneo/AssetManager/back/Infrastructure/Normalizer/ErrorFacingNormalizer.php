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

namespace Akeneo\AssetManager\Infrastructure\Normalizer;

use Akeneo\AssetManager\Domain\Exception\UserFacingError;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorFacingNormalizer
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function normalize(UserFacingError $error, string $propertyPath): array
    {
        return [
            [
                'messageTemplate' => $error->translationId(),
                'parameters' => $error->translationParameters(),
                'message' => $this->translator->trans($error->translationId(), $error->translationParameters(), 'validators'),
                'propertyPath' => $propertyPath,
            ]
        ];
    }
}
