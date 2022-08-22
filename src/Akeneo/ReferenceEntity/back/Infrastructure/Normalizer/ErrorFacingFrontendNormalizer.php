<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Normalizer;

use Akeneo\ReferenceEntity\Domain\Exception\UserFacingError;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorFacingFrontendNormalizer
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function normalize(UserFacingError $error, string $propertyPath): array
    {
        return [
            [
                'messageTemplate' => $error->translationKey(),
                'parameters' => $error->translationParameters(),
                'message' => $this->translator->trans($error->translationKey(), $error->translationParameters(), 'validators'),
                'propertyPath' => $propertyPath,
            ]
        ];
    }
}
