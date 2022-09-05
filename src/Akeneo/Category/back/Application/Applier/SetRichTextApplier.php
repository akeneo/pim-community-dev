<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SetRichTextApplier implements UserIntentApplier
{
    /**
     * @param SetRichText $userIntent
     */
    public function apply(UserIntent $userIntent, Category $category): void
    {
        if (!$userIntent instanceof SetRichText) {
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

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [SetRichText::class];
    }
}
