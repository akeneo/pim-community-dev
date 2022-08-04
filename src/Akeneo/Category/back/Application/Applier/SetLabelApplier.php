<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Category;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetLabelApplier implements UserIntentApplier
{
    public function apply(UserIntent $userIntent, Category $category): void
    {
        Assert::isInstanceOf($userIntent, SetLabel::class);
        $category->setLabel($userIntent->localeCode(), $userIntent->label());
    }

    public function getSupportedUserIntents(): array
    {
        return [SetLabel::class];
    }
}
