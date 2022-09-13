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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\FamilyUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;

class UpsertProductCommandCleaner
{
    private const VALUE_USER_INTENTS_PATH = 'valueUserIntents';
    private const CATEGORY_USER_INTENT_PATH = 'categoryUserIntent';
    private const FAMILY_USER_INTENT_PATH = 'familyUserIntent';
    private const ENABLED_USER_INTENT_PATH = 'enabledUserIntent';

    public static function removeInvalidUserIntents(
        array $violationPropertyPaths,
        UpsertProductCommand $upsertProductCommand,
    ): UpsertProductCommand {
        $valueUserIntents = self::processValueUserIntents($violationPropertyPaths, $upsertProductCommand->valueUserIntents());
        $categoryUserIntent = self::processCategoryUserIntent($violationPropertyPaths, $upsertProductCommand->categoryUserIntent());
        $familyUserIntent = self::processFamilyUserIntent($violationPropertyPaths, $upsertProductCommand->familyUserIntent());
        $enabledUserIntent = self::processEnabledUserIntent($violationPropertyPaths, $upsertProductCommand->enabledUserIntent());

        return UpsertProductCommand::createWithIdentifier(
            userId: $upsertProductCommand->userId(),
            productIdentifier: ProductIdentifier::fromIdentifier($upsertProductCommand->productIdentifierOrUuid()->identifier()),
            userIntents: \array_merge([
                $familyUserIntent,
                $categoryUserIntent,
                $enabledUserIntent,
            ], $valueUserIntents),
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

    private static function processFamilyUserIntent(array $violationPropertyPaths, ?FamilyUserIntent $familyUserIntent): ?FamilyUserIntent
    {
        if (null === $familyUserIntent) {
            return null;
        }

        foreach ($violationPropertyPaths as $propertyPath) {
            if (str_starts_with($propertyPath, self::FAMILY_USER_INTENT_PATH)) {
                return null;
            }
        }

        return $familyUserIntent;
    }

    private static function processEnabledUserIntent(array $violationPropertyPaths, ?SetEnabled $enabledUserIntent): ?SetEnabled
    {
        if (null === $enabledUserIntent) {
            return null;
        }

        foreach ($violationPropertyPaths as $propertyPath) {
            if (str_starts_with($propertyPath, self::ENABLED_USER_INTENT_PATH)) {
                return null;
            }
        }

        return $enabledUserIntent;
    }
}
