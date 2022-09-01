<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Category;
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
            throw new \InvalidArgumentException('Unexpected class');
        }

        $valueCollection = $category->getValueCollection() ?? ValueCollection::fromArray([]);
        $valueCollection->setValue(
            $userIntent->attributeUuid(),
            $userIntent->attributeCode(),
            $userIntent->localeCode(),
            $userIntent->value(),
        );

        $category->setValueCollection($valueCollection);
    }

    public function getSupportedUserIntents(): array
    {
        return [SetTextArea::class];
    }
}
