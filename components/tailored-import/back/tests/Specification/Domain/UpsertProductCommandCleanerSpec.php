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

namespace Specification\Akeneo\Platform\TailoredImport\Domain;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use PhpSpec\ObjectBehavior;

class UpsertProductCommandCleanerSpec extends ObjectBehavior
{
    public function it_cleans_value_user_intents(): void
    {
        $invalidUpsertProductCommand = $this->createUpsertProductCommand([
            new SetTextValue('name', null, null, value: 'A name'),
            new SetTextValue('description', null, null, 'A description with error'),
        ]);

        $expectedUpsertProductCommand = $this->createUpsertProductCommand([
            new SetTextValue('name', null, null, value: 'A name'),
        ]);

        $this::removeInvalidUserIntents(['valueUserIntents[1]'], $invalidUpsertProductCommand)->shouldBeLike($expectedUpsertProductCommand);
    }

    public function it_cleans_category_user_intent(): void
    {
        $invalidUpsertProductCommand = $this->createUpsertProductCommand([new SetCategories(['unknown_category'])]);

        $expectedUpsertProductCommand = $this->createUpsertProductCommand([]);

        $this::removeInvalidUserIntents(['categoryUserIntent'], $invalidUpsertProductCommand)->shouldBeLike($expectedUpsertProductCommand);
    }

    public function it_cleans_family_user_intent(): void
    {
        $invalidUpsertProductCommand = $this->createUpsertProductCommand([new SetFamily('a_family')]);

        $expectedUpsertProductCommand = $this->createUpsertProductCommand([]);

        $this::removeInvalidUserIntents(['familyUserIntent'], $invalidUpsertProductCommand)->shouldBeLike($expectedUpsertProductCommand);
    }

    public function it_cleans_enabled_user_intent(): void
    {
        $invalidUpsertProductCommand = $this->createUpsertProductCommand([new SetEnabled(true)]);

        $expectedUpsertProductCommand = $this->createUpsertProductCommand([]);

        $this::removeInvalidUserIntents(['enabledUserIntent'], $invalidUpsertProductCommand)->shouldBeLike($expectedUpsertProductCommand);
    }

    private function createUpsertProductCommand(array $userIntents): UpsertProductCommand
    {
        return UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: $userIntents,
        );
    }
}
