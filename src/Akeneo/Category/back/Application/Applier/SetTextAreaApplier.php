<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SetTextAreaApplier implements UserIntentApplier
{
    /**
     * @param SetTextArea $userIntent
     */
    public function apply(UserIntent $userIntent, Category $category): void
    {
        if (!$userIntent instanceof SetTextArea) {
            throw new \InvalidArgumentException(sprintf('Unexpected class: %s', get_class($userIntent)));
        }

        $attributes = $category->getAttributes() ?? ValueCollection::fromArray([]);
        $attributes->setValue(
            TextAreaValue::fromApplier(
                value: $userIntent->value(),
                uuid: $userIntent->attributeUuid(),
                code: $userIntent->attributeCode(),
                channel: $userIntent->channelCode(),
                locale: $userIntent->localeCode(),
            ),
        );

        $category->setAttributes($attributes);
    }

    public function getSupportedUserIntents(): array
    {
        return [SetTextArea::class];
    }
}
