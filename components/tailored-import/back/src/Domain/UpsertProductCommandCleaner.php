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

namespace Akeneo\Platform\TailoredImport\Domain;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;

class UpsertProductCommandCleaner
{
    private const VALUE_USER_INTENTS_PATH = 'valueUserIntents';
    private const CATEGORY_USER_INTENT_PATH = 'categoryUserIntent';

    public static function removeInvalidUserIntents(
        array $violationPropertyPaths,
        UpsertProductCommand $upsertProductCommand,
    ): UpsertProductCommand {
        $valueUserIntents = self::processValueUserIntents($violationPropertyPaths, $upsertProductCommand->valueUserIntents());
        $categoryUserIntent = self::processCategoryUserIntent($violationPropertyPaths, $upsertProductCommand->categoryUserIntent());

        return new UpsertProductCommand(
            userId: $upsertProductCommand->userId(),
            productIdentifier: $upsertProductCommand->productIdentifier(),
            categoryUserIntent: $categoryUserIntent,
            valueUserIntents: $valueUserIntents,
        );
    }

    private static function processValueUserIntents(array $violationPropertyPaths, array $valueUserIntents): array
    {
        foreach ($violationPropertyPaths as $propertyPath) {
            if (str_starts_with($propertyPath, self::VALUE_USER_INTENTS_PATH)) {
                $index = substr($propertyPath, strlen(self::VALUE_USER_INTENTS_PATH) + 1, -1);

                unset($valueUserIntents[$index]);
            }
        }

        return array_values($valueUserIntents);
    }

    private static function processCategoryUserIntent(array $violationPropertyPaths, ?CategoryUserIntent $categoryUserIntent): ?CategoryUserIntent
    {
        if (null === $categoryUserIntent) {
            return null;
        }

        foreach ($violationPropertyPaths as $propertyPath) {
            if (str_starts_with($propertyPath, self::CATEGORY_USER_INTENT_PATH)) {
                return null;
            }
        }

        return $categoryUserIntent;
    }
}
